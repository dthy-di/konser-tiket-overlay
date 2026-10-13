<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class OrderController extends Controller
{
    public function show(Order $order, PaymentService $payments)
    {
        $this->ownOrFail($order);

        $order->load('items.ticketType.event', 'payment', 'tickets');

        $midtrans = config('ticketing.midtrans');

        return view('orders.show', [
            'order' => $order,
            'event' => $order->items->first()?->ticketType?->event,
            'driver' => $payments->driver(),
            'snapToken' => $payments->usesMidtrans() && $order->isPending() ? $order->payment?->snap_token : null,
            'clientKey' => $midtrans['client_key'],
            'snapUrl' => $midtrans['is_production']
                ? 'https://app.midtrans.com/snap/snap.js'
                : 'https://app.sandbox.midtrans.com/snap/snap.js',
            'canFakePay' => $this->canFakePay() && $order->isPending(),
        ]);
    }

    /** Dipanggil JavaScript di halaman pesanan tiap beberapa detik untuk mengecek apakah sudah dibayar. */
    public function status(Order $order, PaymentService $payments): JsonResponse
    {
        $this->ownOrFail($order);

        // Webhook tetap jalur utama. Ini cadangan: maksimal 1x tanya ke Midtrans per 20 detik per pesanan.
        if ($order->isPending() && Cache::add("order-sync:{$order->id}", 1, 20)) {
            $payments->syncFromGateway($order);
            $order->refresh();
        }

        return response()->json(['status' => $order->status]);
    }

    /** Tombol "bayar" simulasi. Hanya aktif kalau PAYMENT_DRIVER=fake dan bukan production. */
    public function fakePay(Order $order, PaymentService $payments): RedirectResponse
    {
        $this->ownOrFail($order);
        abort_unless($this->canFakePay(), 404);

        $order->payment?->update(['gateway' => 'fake', 'method' => 'simulasi']);
        $payments->markPaid($order);

        return redirect()->route('orders.show', $order);
    }

    private function canFakePay(): bool
    {
        return config('ticketing.payment_driver') === 'fake' && ! app()->isProduction();
    }

    private function ownOrFail(Order $order): void
    {
        abort_unless($order->user_id === Auth::id(), 404);
    }
}
