<?php

namespace App\Http\Controllers;

use App\Exceptions\CheckoutException;
use App\Models\Event;
use App\Models\Order;
use App\Services\CheckoutService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class CheckoutController extends Controller
{
    public function store(
        Request $request,
        Event $event,
        CheckoutService $checkout,
        PaymentService $payments
    ): RedirectResponse {
        $data = $request->validate([
            'qty' => ['required', 'array'],
            'qty.*' => ['nullable', 'integer', 'min:0', 'max:'.config('ticketing.max_per_order')],
        ], [
            'qty.required' => 'Pilih minimal 1 tiket.',
            'qty.*.integer' => 'Jumlah tiket tidak valid.',
            'qty.*.max' => 'Maksimal :max tiket per kategori.',
        ]);

        $quantities = [];
        foreach ($data['qty'] as $typeId => $qty) {
            $quantities[(int) $typeId] = (int) $qty;
        }

        try {
            $order = $checkout->createOrder($request->user(), $event, $quantities);
        } catch (CheckoutException $e) {
            return back()->withErrors(['qty' => $e->getMessage()])->withInput();
        }

        try {
            $payments->initiate($order);
        } catch (Throwable $e) {
            // Gateway bermasalah: batalkan pesanan supaya stok langsung kembali.
            report($e);
            $payments->closeOrder($order, Order::CANCELLED);

            return back()
                ->withErrors(['qty' => 'Layanan pembayaran sedang bermasalah. Silakan coba lagi sebentar lagi.'])
                ->withInput();
        }

        return redirect()->route('orders.show', $order);
    }
}
