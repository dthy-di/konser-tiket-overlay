<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Support\QrRenderer;
use App\Support\TicketCode;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = Ticket::with(['event', 'ticketType'])
            ->whereHas('order', fn ($query) => $query->where('user_id', Auth::id()))
            ->latest('id')
            ->get();

        return view('tickets.index', compact('tickets'));
    }

    public function show(Ticket $ticket, TicketCode $codes, QrRenderer $qr)
    {
        $ticket->load('event', 'ticketType', 'order');

        abort_unless($ticket->order->user_id === Auth::id(), 404);

        return view('tickets.show', [
            'ticket' => $ticket,
            'qrSvg' => $ticket->status === Ticket::ACTIVE
                ? $qr->svg($codes->payload($ticket->code))
                : null,
        ]);
    }
}
