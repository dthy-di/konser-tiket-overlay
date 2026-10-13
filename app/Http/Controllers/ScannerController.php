<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\ScanLog;
use App\Models\Ticket;
use App\Models\User;
use App\Support\TicketCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScannerController extends Controller
{
    public function index()
    {
        $events = Event::published()->orderBy('starts_at')->get(['id', 'name', 'starts_at']);

        // Pilih otomatis event yang berlangsung hari ini, atau yang terdekat.
        $default = $events->first(fn ($event) => $event->starts_at->isToday())
            ?? $events->first(fn ($event) => $event->starts_at->isFuture());

        return view('scanner.index', compact('events', 'default'));
    }

    /** Validasi satu tiket. Dipanggil JavaScript setiap kali kamera membaca QR. */
    public function check(Request $request, TicketCode $codes): JsonResponse
    {
        $data = $request->validate([
            'payload' => ['required', 'string', 'max:120'],
            'event_id' => ['required', 'integer', 'exists:events,id'],
        ]);

        $eventId = (int) $data['event_id'];
        $staff = $request->user();
        $input = $data['payload'];

        $parsed = $codes->parse($input);

        if (! $parsed) {
            return $this->result('invalid', 'QR tidak dikenali atau tanda tangan tidak cocok (kemungkinan tiket palsu).', null, $eventId, $staff, $input);
        }

        return DB::transaction(function () use ($parsed, $eventId, $staff, $input) {
            $ticket = Ticket::with(['ticketType', 'event'])
                ->where('code', $parsed['code'])
                ->lockForUpdate()
                ->first();

            if (! $ticket) {
                return $this->result('invalid', 'Tiket tidak ditemukan.', null, $eventId, $staff, $input);
            }

            if ((int) $ticket->event_id !== $eventId) {
                return $this->result('wrong_event', "Tiket ini untuk event lain: {$ticket->event->name}.", $ticket, $eventId, $staff, $input);
            }

            if ($ticket->status === Ticket::VOID) {
                return $this->result('void', 'Tiket sudah dibatalkan (refund).', $ticket, $eventId, $staff, $input);
            }

            if ($ticket->status === Ticket::USED) {
                return $this->result('duplicate', 'Tiket ini sudah pernah dipakai masuk.', $ticket, $eventId, $staff, $input);
            }

            // Penentu akhir: hanya satu proses yang bisa mengubah active -> used.
            $now = now();
            $updated = Ticket::whereKey($ticket->id)
                ->where('status', Ticket::ACTIVE)
                ->update(['status' => Ticket::USED, 'used_at' => $now, 'scanned_by' => $staff->id]);

            if ($updated === 0) {
                $ticket->refresh();

                return $this->result('duplicate', 'Tiket ini sudah pernah dipakai masuk.', $ticket, $eventId, $staff, $input);
            }

            $ticket->forceFill(['status' => Ticket::USED, 'used_at' => $now])->syncOriginal();

            return $this->result('valid', 'Tiket valid. Silakan masuk.', $ticket, $eventId, $staff, $input);
        });
    }

    private function result(string $result, string $message, ?Ticket $ticket, int $eventId, User $staff, string $input): JsonResponse
    {
        ScanLog::create([
            'ticket_id' => $ticket?->id,
            'event_id' => $eventId,
            'user_id' => $staff->id,
            'result' => $result,
            'input' => mb_substr($input, 0, 120),
        ]);

        return response()->json([
            'result' => $result,
            'message' => $message,
            'ticket' => $ticket ? [
                'code' => $ticket->code,
                'type' => $ticket->ticketType->name,
                'holder' => $ticket->holder_name,
                'event' => $ticket->event->name,
                'used_at' => $ticket->used_at?->translatedFormat('d M Y H:i'),
            ] : null,
        ]);
    }
}
