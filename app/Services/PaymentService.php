<?php

namespace App\Services;

use App\Exceptions\CheckoutException;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Support\TicketCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;
use Midtrans\Transaction;
use RuntimeException;
use Throwable;

/**
 * Semua perubahan status pesanan/pembayaran lewat sini.
 * Setiap method dibuat IDEMPOTEN: webhook yang terkirim 2x atau lebih tidak membuat tiket ganda.
 */
class PaymentService
{
    public function __construct(private TicketCode $codes)
    {
    }

    public function driver(): string
    {
        return (string) config('ticketing.payment_driver');
    }

    public function usesMidtrans(): bool
    {
        return $this->driver() === 'midtrans';
    }

    /** Siapkan pembayaran untuk pesanan baru. Untuk Midtrans: membuat Snap token. */
    public function initiate(Order $order): void
    {
        $order->payment->update(['gateway' => $this->driver()]);

        if (! $this->usesMidtrans()) {
            return;
        }

        if (! config('ticketing.midtrans.server_key')) {
            throw new RuntimeException('MIDTRANS_SERVER_KEY belum diisi di file .env');
        }

        $this->configureMidtrans();
        $order->loadMissing('user', 'items.ticketType');

        $token = Snap::getSnapToken([
            'transaction_details' => [
                'order_id' => $order->code,
                'gross_amount' => (int) $order->total,
            ],
            'enabled_payments' => [
            'qris',
            'bank_transfer',
            'gopay',
            'shopeepay',
            ],
            'customer_details' => array_filter([
                'first_name' => $order->user->name,
                'email' => $order->user->email,
                'phone' => $order->user->phone,
            ]),
            // Jumlah (harga x qty) semua item harus sama dengan gross_amount.
            'item_details' => $order->items->map(fn ($item) => [
                'id' => 'TT'.$item->ticket_type_id,
                'price' => (int) $item->unit_price,
                'quantity' => (int) $item->quantity,
                'name' => Str::limit($item->ticketType->name, 50, ''),
            ])->values()->all(),
            // Waktu bayar di sisi Midtrans disamakan dengan waktu pesanan kita.
            'expiry' => [
                'unit' => 'minute',
                'duration' => (int) config('ticketing.order_ttl_minutes'),
            ],
            'callbacks' => ['finish' => route('orders.show', $order)],
        ]);

        $order->payment->update(['snap_token' => $token]);
    }

    /**
     * Terapkan status dari gateway (dari webhook ATAU dari pengecekan status manual).
     *
     * @param  array<string,mixed>  $data  balasan/notifikasi Midtrans
     */
    public function applyGatewayStatus(Order $order, array $data): void
    {
        $status = $data['transaction_status'] ?? null;
        $fraud = $data['fraud_status'] ?? null;

        $order->payment?->update(array_filter([
            'method' => $data['payment_type'] ?? null,
            'gateway_ref' => $data['transaction_id'] ?? null,
            'payload' => $data,
        ], fn ($value) => $value !== null));

        if ($status === 'settlement' || ($status === 'capture' && $fraud === 'accept')) {
            $this->markPaid($order);
        } elseif (in_array($status, ['deny', 'cancel', 'failure'], true)) {
            $this->closeOrder($order, Order::CANCELLED);
        } elseif ($status === 'expire') {
            $this->closeOrder($order, Order::EXPIRED);
        } elseif (in_array($status, ['refund', 'chargeback'], true)) {
            $this->refund($order);
        }
        // "pending" atau "capture"+"challenge": tunggu notifikasi berikutnya.
        // Refund sebagian (partial_refund) sengaja diabaikan, tangani manual di dashboard Midtrans.
    }

    /** Pembayaran sukses: ubah status, terbitkan tiket. Aman dipanggil berulang kali. */
    public function markPaid(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (in_array($order->status, [Order::EXPIRED, Order::CANCELLED], true)) {
                // Uang masuk setelah pesanan kedaluwarsa. Stok sudah dilepas, coba ambil lagi.
                try {
                    DB::transaction(fn () => $this->reserveStock($order));
                } catch (CheckoutException) {
                    $order->update(['status' => Order::REFUND_NEEDED]);
                    $order->payment?->update(['status' => Order::REFUND_NEEDED]);
                    Log::warning("Pembayaran terlambat untuk {$order->code} dan stok sudah habis: refund manual diperlukan.");

                    return;
                }
            } elseif ($order->status !== Order::PENDING) {
                return; // sudah paid / refunded / refund_needed
            }

            $order->update(['status' => Order::PAID, 'paid_at' => now()]);
            $order->payment?->update(['status' => 'paid', 'paid_at' => now()]);

            $this->issueTickets($order);
        });
    }

    /** Pesanan gagal / dibatalkan / kedaluwarsa: kembalikan stok. Hanya berlaku untuk pesanan pending. */
    public function closeOrder(Order $order, string $newStatus): void
    {
        DB::transaction(function () use ($order, $newStatus) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($order->status !== Order::PENDING) {
                return;
            }

            $this->releaseStock($order);
            $order->update(['status' => $newStatus]);
            $order->payment?->update(['status' => $newStatus]);
        });
    }

    /** Dana dikembalikan: batalkan tiket yang belum dipakai dan kembalikan stok. */
    public function refund(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($order->status === Order::PAID) {
                $this->releaseStock($order);
            } elseif ($order->status !== Order::REFUND_NEEDED) {
                return;
            }

            $order->tickets()->where('status', Ticket::ACTIVE)->update(['status' => Ticket::VOID]);
            $order->update(['status' => Order::REFUNDED]);
            $order->payment?->update(['status' => Order::REFUNDED]);
        });
    }

    /** Tanya status ke Midtrans. Berguna kalau webhook belum/tidak sampai (mis. saat tes di localhost). */
    public function syncFromGateway(Order $order): void
    {
        if (! $this->usesMidtrans()) {
            return;
        }

        $this->configureMidtrans();

        try {
            $result = Transaction::status($order->code);
        } catch (Throwable $e) {
            // 404 = pembeli belum memilih metode pembayaran, itu normal.
            if ($e->getCode() !== 404) {
                Log::warning("Gagal cek status Midtrans untuk {$order->code}: ".$e->getMessage());
            }

            return;
        }

        $this->applyGatewayStatus($order, (array) $result);
    }

    /** Dipanggil scheduler tiap menit. Return: jumlah pesanan yang diakhiri. */
    public function expirePending(): int
    {
        $count = 0;

        Order::where('status', Order::PENDING)
            ->where('expires_at', '<', now())
            ->chunkById(200, function ($orders) use (&$count) {
                foreach ($orders as $order) {
                    // Cek dulu ke gateway: jangan sampai pesanan yang sebenarnya sudah dibayar ikut kedaluwarsa.
                    $this->syncFromGateway($order);

                    $order->refresh();

                    if ($order->status === Order::PENDING) {
                        $this->closeOrder($order, Order::EXPIRED);
                        $count++;
                    }
                }
            });

        return $count;
    }

    // ---------------------------------------------------------------- internal

    private function configureMidtrans(): void
    {
        MidtransConfig::$serverKey = (string) config('ticketing.midtrans.server_key');
        MidtransConfig::$isProduction = (bool) config('ticketing.midtrans.is_production');
        MidtransConfig::$isSanitized = true;
        MidtransConfig::$is3ds = true;
    }

    private function issueTickets(Order $order): void
    {
        if ($order->tickets()->exists()) {
            return; // sudah pernah diterbitkan
        }

        $order->loadMissing('user');

        foreach ($order->items()->with('ticketType')->get() as $item) {
            for ($i = 0; $i < $item->quantity; $i++) {
                Ticket::create([
                    'order_id' => $order->id,
                    'ticket_type_id' => $item->ticket_type_id,
                    'event_id' => $item->ticketType->event_id,
                    'code' => $this->codes->generate(),
                    'holder_name' => $order->user->name,
                    'status' => Ticket::ACTIVE,
                ]);
            }
        }
    }

    private function releaseStock(Order $order): void
    {
        foreach ($order->items as $item) {
            TicketType::whereKey($item->ticket_type_id)
                ->where('sold', '>=', $item->quantity)
                ->decrement('sold', $item->quantity);
        }
    }

    /** @throws CheckoutException kalau stok tidak cukup (perubahan sebelumnya otomatis dibatalkan oleh transaksi) */
    private function reserveStock(Order $order): void
    {
        $items = $order->items->sortBy('ticket_type_id');

        foreach ($items as $item) {
            $reserved = TicketType::whereKey($item->ticket_type_id)
                ->whereRaw('sold + ? <= quota', [$item->quantity])
                ->increment('sold', $item->quantity);

            if ($reserved === 0) {
                throw new CheckoutException('Stok tidak cukup.');
            }
        }
    }
}
