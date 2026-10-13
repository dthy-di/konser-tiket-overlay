<?php

return [

    // 'fake'     = tombol "bayar" simulasi, untuk tes lokal tanpa akun payment gateway
    // 'midtrans' = pembayaran asli lewat Midtrans (VA semua bank, QRIS, e-wallet, kartu, minimarket)
    'payment_driver' => env('PAYMENT_DRIVER', 'fake'),

    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'is_production' => (bool) env('MIDTRANS_IS_PRODUCTION', false),
    ],

    // Waktu pembeli untuk membayar. Lewat dari ini pesanan kedaluwarsa dan stok dilepas lagi.
    'order_ttl_minutes' => (int) env('ORDER_TTL_MINUTES', 15),

    // Maksimal tiket dalam satu pesanan
    'max_per_order' => (int) env('MAX_TICKETS_PER_ORDER', 4),

    // Maksimal pesanan "belum dibayar" per pengguna (mencegah menimbun stok)
    'max_pending_orders' => (int) env('MAX_PENDING_ORDERS', 2),

    // Kunci rahasia tanda tangan QR. Default memakai APP_KEY.
    // JANGAN diubah setelah tiket terbit, kalau tidak semua QR lama jadi tidak valid.
    'qr_secret' => env('TICKET_QR_SECRET') ?: env('APP_KEY'),

];
