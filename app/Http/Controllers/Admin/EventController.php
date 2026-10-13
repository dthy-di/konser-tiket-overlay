<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EventController extends Controller
{
    /**
     * Menampilkan form pembuatan event.
     */
    public function create(): View
    {
        return view('admin.events.create');
    }

    /**
     * Menyimpan event baru beserta jenis tiketnya.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'                => ['required', 'string', 'max:255'],
            'description'          => ['nullable', 'string'],
            'venue'                => ['required', 'string', 'max:255'],
            'starts_at'            => ['required', 'date', 'after:now'],
            'ticket_types'         => ['required', 'array', 'min:1'],
            'ticket_types.*.name'  => ['required', 'string', 'max:100'],
            'ticket_types.*.price' => ['required', 'integer', 'min:0'],
            'ticket_types.*.quota' => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($data) {
            $event = Event::create([
                'name'        => $data['title'],
                'description' => $data['description'] ?? null,
                'venue'       => $data['venue'],
                'starts_at'   => $data['starts_at'],
                'status'      => Event::DRAFT,
            ]);

            $event->ticketTypes()->createMany($data['ticket_types']);
        });

        return redirect()
            ->route('admin.dashboard')
            ->with('status', 'Event berhasil dibuat.');
    }

    /**
     * Membuka atau menutup penjualan event.
     */
    public function toggle(Event $event): RedirectResponse
    {
        $event->update([
            'status' => $event->status === Event::PUBLISHED
                ? Event::DRAFT
                : Event::PUBLISHED,
        ]);

        return redirect()
            ->route('admin.dashboard')
            ->with('status', 'Status event berhasil diperbarui.');
    }

    /**
     * Menghapus event beserta data terkaitnya.
     */
    public function destroy(Event $event): RedirectResponse
    {
        try {
            DB::transaction(function () use ($event) {
                $eventId = $event->getKey();

                // Ambil ID jenis tiket milik event.
                $ticketTypeIds = DB::table('ticket_types')
                    ->where('event_id', $eventId)
                    ->pluck('id');

                // Ambil ID tiket milik event.
                $ticketIds = DB::table('tickets')
                    ->where('event_id', $eventId)
                    ->pluck('id');

                // Ambil pesanan yang mempunyai item tiket event ini.
                $orderIds = DB::table('order_items')
                    ->whereIn('ticket_type_id', $ticketTypeIds)
                    ->pluck('order_id')
                    ->unique();

                // Hapus log check-in terkait event dan tiket.
                DB::table('scan_logs')
                    ->where('event_id', $eventId)
                    ->delete();

                DB::table('scan_logs')
                    ->whereIn('ticket_id', $ticketIds)
                    ->delete();

                // Hapus tiket sebelum jenis tiket dan event.
                DB::table('tickets')
                    ->where('event_id', $eventId)
                    ->delete();

                // Hapus item pesanan terkait tiket event.
                DB::table('order_items')
                    ->whereIn('ticket_type_id', $ticketTypeIds)
                    ->delete();

                // Hapus pembayaran dan pesanan terkait.
                DB::table('payments')
                    ->whereIn('order_id', $orderIds)
                    ->delete();

                DB::table('orders')
                    ->whereIn('id', $orderIds)
                    ->delete();

                // Hapus jenis tiket.
                DB::table('ticket_types')
                    ->where('event_id', $eventId)
                    ->delete();

                // Terakhir, hapus event.
                DB::table('events')
                    ->where('id', $eventId)
                    ->delete();
            });
        } catch (QueryException $e) {
            report($e);

            return redirect()
                ->route('admin.dashboard')
                ->with(
                    'error',
                    'Event gagal dihapus. Periksa storage/logs/laravel.log.'
                );
        }

        return redirect()
            ->route('admin.dashboard')
            ->with('status', 'Event beserta data terkait berhasil dihapus.');
    }
}