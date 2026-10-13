<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Notifikasi pembayaran dari Midtrans (HTTP Notification / webhook).
     * URL yang diisi di dashboard Midtrans:  https://DOMAIN-KAMU/api/webhooks/midtrans
     */
    public function midtrans(Request $request, PaymentService $payments): JsonResponse
    {
        $data = $request->all();

        $orderId = $data['order_id'] ?? null;
        if (! is_string($orderId)) {
            return response()->json(['message' => 'Bad request'], 400);
        }

        // 1) Verifikasi signature: SHA512(order_id + status_code + gross_amount + server_key).
        //    Tanpa ini siapa saja bisa mengirim "pembayaran sukses" palsu ke URL ini.
        $serverKey = (string) config('ticketing.midtrans.server_key');
        $expected = hash('sha512', $orderId.($data['status_code'] ?? '').($data['gross_amount'] ?? '').$serverKey);

        if ($serverKey === '' || ! hash_equals($expected, (string) ($data['signature_key'] ?? ''))) {
            Log::warning('Webhook Midtrans ditolak: signature tidak valid', ['order_id' => $orderId]);

            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // 2) Cari pesanan. Notifikasi tes dari dashboard Midtrans memakai order_id acak: abaikan saja.
        $order = Order::where('code', $orderId)->first();
        if (! $order) {
            return response()->json(['message' => 'Order not found, ignored']);
        }

        // 3) Pastikan nominal sama dengan pesanan kita.
        if ((int) round((float) ($data['gross_amount'] ?? 0)) !== (int) $order->total) {
            Log::warning('Webhook Midtrans: nominal tidak cocok', ['order' => $order->code, 'gross_amount' => $data['gross_amount'] ?? null]);

            return response()->json(['message' => 'Amount mismatch'], 422);
        }

        // 4) Terapkan status (idempotent, aman kalau Midtrans mengirim berkali-kali).
        $payments->applyGatewayStatus($order, $data);

        return response()->json(['message' => 'OK']);
    }
}
