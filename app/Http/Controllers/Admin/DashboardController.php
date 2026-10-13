<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Ticket;

class DashboardController extends Controller
{
    public function index()
    {
        $events = Event::with('ticketTypes')
            ->withCount([
                'tickets as tickets_issued' => fn ($query) => $query->where('status', '!=', Ticket::VOID),
                'tickets as tickets_used' => fn ($query) => $query->where('status', Ticket::USED),
            ])
            ->orderByDesc('starts_at')
            ->get();

        // Pendapatan per event: jumlah (qty x harga) dari pesanan lunas.
        $revenue = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('ticket_types', 'ticket_types.id', '=', 'order_items.ticket_type_id')
            ->where('orders.status', Order::PAID)
            ->groupBy('ticket_types.event_id')
            ->selectRaw('ticket_types.event_id as event_id, SUM(order_items.quantity * order_items.unit_price) as revenue')
            ->pluck('revenue', 'event_id');

        $stats = [
            'revenue' => (int) Order::where('status', Order::PAID)->sum('total'),
            'tickets' => Ticket::where('status', '!=', Ticket::VOID)->count(),
            'pending' => Order::where('status', Order::PENDING)->count(),
            'refund_needed' => Order::where('status', Order::REFUND_NEEDED)->count(),
        ];

        return view('admin.dashboard', compact('events', 'revenue', 'stats'));
    }
}
