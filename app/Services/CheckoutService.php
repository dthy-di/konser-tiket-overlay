<?php

namespace App\Services;

use App\Exceptions\CheckoutException;
use App\Models\Event;
use App\Models\Order;
use App\Models\Payment;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    /**
     * Membuat pesanan dan MENAHAN stok secara atomik.
     *
     * Kunci anti-oversell: update stok memakai satu query
     *   UPDATE ticket_types SET sold = sold + N WHERE id = ? AND sold + N <= quota
     * Kalau 0 baris berubah berarti stok tidak cukup. Database yang menjamin ini,
     * jadi 1000 orang klik bersamaan pun tidak akan melebihi kuota.
     *
     * @param  array<int,int>  $quantities  [ticket_type_id => jumlah]
     *
     * @throws CheckoutException
     */
    public function createOrder(User $user, Event $event, array $quantities): Order
    {
        $quantities = array_filter($quantities, fn ($qty) => $qty > 0);

        if ($quantities === []) {
            throw new CheckoutException('Pilih minimal 1 tiket.');
        }

        $max = (int) config('ticketing.max_per_order');
        if (array_sum($quantities) > $max) {
            throw new CheckoutException("Maksimal {$max} tiket per pesanan.");
        }

        if ($event->status !== Event::PUBLISHED || $event->starts_at->isPast()) {
            throw new CheckoutException('Penjualan tiket untuk event ini sudah ditutup.');
        }

        $pending = Order::where('user_id', $user->id)->where('status', Order::PENDING)->count();
        if ($pending >= (int) config('ticketing.max_pending_orders')) {
            throw new CheckoutException('Selesaikan dulu pesanan yang belum dibayar, atau tunggu sampai kedaluwarsa.');
        }

        // Urutan konsisten antar-pesanan supaya tidak terjadi deadlock di database.
        ksort($quantities);

        return DB::transaction(function () use ($user, $event, $quantities) {
            $types = TicketType::where('event_id', $event->id)
                ->whereIn('id', array_keys($quantities))
                ->get()
                ->keyBy('id');

            if ($types->count() !== count($quantities)) {
                throw new CheckoutException('Kategori tiket tidak valid.');
            }

            $total = 0;

            foreach ($quantities as $typeId => $qty) {
                $type = $types[$typeId];

                if (! $type->isOnSale()) {
                    throw new CheckoutException("Penjualan tiket {$type->name} belum dibuka atau sudah ditutup.");
                }

                $reserved = TicketType::whereKey($typeId)
                    ->whereRaw('sold + ? <= quota', [$qty])
                    ->increment('sold', $qty);

                if ($reserved === 0) {
                    throw new CheckoutException("Maaf, tiket {$type->name} tidak cukup atau sudah habis.");
                }

                $total += $type->price * $qty;
            }

            $order = Order::create([
                'user_id' => $user->id,
                'code' => Order::newCode(),
                'status' => Order::PENDING,
                'total' => $total,
                'expires_at' => now()->addMinutes((int) config('ticketing.order_ttl_minutes')),
            ]);

            foreach ($quantities as $typeId => $qty) {
                $order->items()->create([
                    'ticket_type_id' => $typeId,
                    'quantity' => $qty,
                    'unit_price' => $types[$typeId]->price,
                ]);
            }

            Payment::create([
                'order_id' => $order->id,
                'gateway' => config('ticketing.payment_driver'),
                'status' => 'pending',
            ]);

            return $order;
        }, 3); // coba ulang sampai 3x kalau database mendeteksi deadlock
    }
}
