
@extends('layouts.app')

@section('title', 'Pesanan '.$order->code)

@section('content')
@php
    $labels = [
        'pending'       => ['Menunggu pembayaran', 'border-amber-400/20 bg-amber-400/10 text-amber-300', '⏳'],
        'paid'          => ['Lunas', 'border-emerald-400/20 bg-emerald-400/10 text-emerald-300', '✓'],
        'expired'       => ['Kedaluwarsa', 'border-slate-400/20 bg-slate-400/10 text-slate-300', '⌛'],
        'cancelled'     => ['Dibatalkan', 'border-slate-400/20 bg-slate-400/10 text-slate-300', '×'],
        'refund_needed' => ['Perlu refund', 'border-red-400/20 bg-red-400/10 text-red-300', '!'],
        'refunded'      => ['Dikembalikan', 'border-slate-400/20 bg-slate-400/10 text-slate-300', '↩'],
    ];

    [$label, $badge, $statusIcon] = $labels[$order->status]
        ?? [$order->status, 'border-slate-700 bg-slate-800 text-slate-200', '•'];
@endphp

<div class="mx-auto max-w-5xl space-y-6 pb-10">

    {{-- HEADER --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ $event ? route('events.show', $event) : route('tickets.index') }}"
               class="inline-flex items-center gap-2 text-sm text-slate-400 transition hover:text-fuchsia-300">
                <span>←</span>
                <span>Kembali</span>
            </a>

            <p class="mt-5 text-xs font-bold uppercase tracking-[0.3em] text-fuchsia-400">
                Order Details
            </p>

            <h1 class="mt-2 text-3xl font-black text-white sm:text-4xl">
                Detail Pesanan
            </h1>

            <p class="mt-2 text-sm text-slate-400">
                Periksa detail tiket dan status pembayaran kamu.
            </p>
        </div>

        <span class="inline-flex w-fit items-center gap-2 rounded-full border px-4 py-2 text-sm font-bold {{ $badge }}">
            <span>{{ $statusIcon }}</span>
            {{ $label }}
        </span>
    </div>

    {{-- ORDER BANNER --}}
    <section class="relative overflow-hidden rounded-3xl border border-white/10 bg-slate-900 shadow-xl">
        <div class="absolute -right-16 -top-24 h-64 w-64 rounded-full bg-fuchsia-600/15 blur-3xl"></div>
        <div class="absolute -bottom-24 left-1/3 h-56 w-56 rounded-full bg-indigo-600/15 blur-3xl"></div>

        <div class="relative p-6 sm:p-8">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-sm text-slate-400">Kode pesanan</p>

                    <div class="mt-2 flex flex-wrap items-center gap-3">
                        <p class="break-all font-mono text-lg font-bold tracking-wide text-white sm:text-xl">
                            {{ $order->code }}
                        </p>

                        <button type="button"
                                id="copy-order-code"
                                class="rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold text-slate-300 transition hover:bg-white/10 hover:text-white">
                            Salin kode
                        </button>
                    </div>

                    <p class="mt-3 text-sm leading-6 text-slate-400">
                        Gunakan kode ini jika kamu perlu memeriksa pesanan atau menghubungi panitia.
                    </p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-black/20 p-4 sm:min-w-[190px]">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                        Total pesanan
                    </p>
                    <p class="mt-2 text-2xl font-black text-white sm:text-3xl">
                        @rupiah($order->total)
                    </p>
                    <p class="mt-2 text-xs text-slate-400">
                        {{ $order->items->sum('quantity') }} tiket
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- MAIN CONTENT --}}
    <div class="grid items-start gap-6 lg:grid-cols-5">

        {{-- LEFT COLUMN --}}
        <div class="space-y-6 lg:col-span-3">

            {{-- EVENT INFO --}}
            @if($event)
                <section class="overflow-hidden rounded-2xl border border-white/10 bg-slate-900">
                    <div class="border-b border-white/10 px-5 py-4 sm:px-6">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-fuchsia-400">
                            Event Information
                        </p>
                        <h2 class="mt-1 text-lg font-extrabold text-white">
                            Informasi Acara
                        </h2>
                    </div>

                    <div class="p-5 sm:p-6">
                        <div class="flex items-start gap-4">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-fuchsia-600 to-indigo-700 text-2xl shadow-lg">
                                🎵
                            </div>

                            <div class="min-w-0 flex-1">
                                <h3 class="text-lg font-extrabold text-white sm:text-xl">
                                    {{ $event->name }}
                                </h3>

                                <div class="mt-3 space-y-2 text-sm text-slate-300">
                                    <p class="flex items-start gap-2">
                                        <span>📅</span>
                                        <span>{{ $event->starts_at->translatedFormat('l, d F Y · H:i') }} WIB</span>
                                    </p>

                                    <p class="flex items-start gap-2">
                                        <span>📍</span>
                                        <span>{{ $event->venue }}@if($event->city), {{ $event->city }}@endif</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            @endif

            {{-- TICKET BREAKDOWN --}}
            <section class="overflow-hidden rounded-2xl border border-white/10 bg-slate-900">
                <div class="flex items-center justify-between gap-3 border-b border-white/10 px-5 py-4 sm:px-6">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-fuchsia-400">
                            Order Summary
                        </p>
                        <h2 class="mt-1 text-lg font-extrabold text-white">
                            Rincian Tiket
                        </h2>
                    </div>

                    <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs font-semibold text-slate-300">
                        {{ $order->items->sum('quantity') }} tiket
                    </span>
                </div>

                <div class="divide-y divide-white/10 px-5 sm:px-6">
                    @foreach($order->items as $item)
                        <div class="flex items-center gap-4 py-5">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-fuchsia-500/15 to-indigo-500/15 text-xl">
                                🎫
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-white">
                                    {{ $item->ticketType->name }}
                                </p>
                                <p class="mt-1 text-sm text-slate-400">
                                    @rupiah($item->unit_price) × {{ $item->quantity }}
                                </p>
                            </div>

                            <p class="shrink-0 text-right font-bold text-white">
                                @rupiah($item->unit_price * $item->quantity)
                            </p>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-white/10 bg-white/[0.03] px-5 py-5 sm:px-6">
                    <div class="flex items-center justify-between text-sm text-slate-400">
                        <span>Jumlah tiket</span>
                        <span>{{ $order->items->sum('quantity') }} tiket</span>
                    </div>

                    <div class="mt-4 flex items-center justify-between gap-4">
                        <span class="font-bold text-white">Total pembayaran</span>
                        <span class="text-2xl font-black text-fuchsia-300">
                            @rupiah($order->total)
                        </span>
                    </div>
                </div>
            </section>
        </div>

        {{-- RIGHT COLUMN --}}
        <aside class="space-y-6 lg:sticky lg:top-6 lg:col-span-2">

            {{-- PENDING PAYMENT --}}
            @if($order->isPending())
                <section class="overflow-hidden rounded-2xl border border-amber-400/20 bg-slate-900 shadow-xl">
                    <div class="border-b border-amber-400/10 bg-gradient-to-r from-amber-500/10 to-fuchsia-500/10 p-5">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-400/10 text-xl">
                                ⏳
                            </div>
                            <div>
                                <h2 class="font-extrabold text-white">Selesaikan Pembayaran</h2>
                                <p class="mt-1 text-xs text-amber-300">Pesanan menunggu pembayaran</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-5">
                        <p class="text-sm leading-6 text-slate-400">
                            Selesaikan pembayaran sebelum waktu habis agar pesanan tetap dapat diproses.
                        </p>

                        <div class="mt-5 rounded-2xl border border-amber-400/20 bg-amber-400/5 p-4 text-center">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-300">
                                Sisa waktu pembayaran
                            </p>

                            <p id="countdown" class="mt-2 font-mono text-4xl font-black tracking-wider text-white">
                                --:--
                            </p>

                            <p id="countdown-message" class="mt-2 text-xs text-slate-400">
                                Selesaikan transaksi sebelum batas waktu berakhir.
                            </p>
                        </div>

                        @if($driver === 'midtrans')
                            @if($snapToken)
                                <button id="pay-button"
                                        type="button"
                                        class="mt-5 flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-fuchsia-600 to-indigo-600 px-5 py-4 font-extrabold text-white shadow-lg shadow-fuchsia-950/30 transition hover:brightness-110 active:scale-[0.99]">
                                    <span>🔒</span>
                                    <span>Pilih Metode &amp; Bayar</span>
                                </button>

                                <div class="mt-4 rounded-xl border border-white/10 bg-white/[0.03] p-4">
                                    <p class="text-sm font-bold text-white">Metode pembayaran</p>
                                    <p class="mt-2 text-xs leading-6 text-slate-400">
                                        Pilihan yang tersedia akan ditampilkan pada halaman pembayaran. Ketersediaannya mengikuti konfigurasi akun merchant.
                                    </p>

                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <span class="rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-xs text-slate-300">QRIS</span>
                                        <span class="rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-xs text-slate-300">Virtual Account</span>
                                        <span class="rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-xs text-slate-300">E-Wallet</span>
                                    </div>
                                </div>
                            @else
                                <div class="mt-5 rounded-xl border border-red-400/20 bg-red-400/5 p-4">
                                    <p class="font-bold text-red-300">Token pembayaran belum tersedia</p>
                                    <p class="mt-1 text-sm leading-6 text-slate-400">
                                        Muat ulang halaman ini. Jika masalah berlanjut, periksa konfigurasi pembayaran atau hubungi panitia.
                                    </p>
                                    <button type="button"
                                            onclick="location.reload()"
                                            class="btn-ghost mt-3 w-full">
                                        Muat Ulang Halaman
                                    </button>
                                </div>
                            @endif

                        @elseif($canFakePay)
                            <form method="POST" action="{{ route('orders.fake-pay', $order) }}">
                                @csrf
                                <button type="submit" class="btn-primary mt-5 w-full">
                                    Simulasi: Bayar Sekarang
                                </button>
                            </form>

                            <div class="mt-3 rounded-xl border border-amber-400/20 bg-amber-400/5 p-3">
                                <p class="text-xs leading-5 text-amber-300">
                                    Mode demo. Tidak ada uang yang berpindah. Gunakan konfigurasi Midtrans untuk menerima pembayaran asli.
                                </p>
                            </div>
                        @endif

                        <div class="mt-5 flex items-start gap-3 border-t border-white/10 pt-4">
                            <span class="text-lg">🛡️</span>
                            <p class="text-xs leading-5 text-slate-400">
                                Jangan tutup halaman sebelum proses pembayaran selesai. Status pesanan akan diperbarui setelah server mengonfirmasi transaksi.
                            </p>
                        </div>
                    </div>
                </section>

            {{-- PAID --}}
            @elseif($order->status === 'paid')
                <section class="overflow-hidden rounded-2xl border border-emerald-400/20 bg-slate-900">
                    <div class="bg-gradient-to-br from-emerald-500/15 to-teal-500/5 p-6 text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full border border-emerald-400/30 bg-emerald-400/10 text-3xl text-emerald-300">
                            ✓
                        </div>

                        <h2 class="mt-4 text-xl font-black text-white">
                            Pembayaran Berhasil!
                        </h2>

                        <p class="mt-2 text-sm leading-6 text-slate-400">
                            Terima kasih. Pesanan kamu sudah lunas dan tiket telah diterbitkan.
                        </p>
                    </div>

                    <div class="space-y-3 p-5">
                        <div class="flex items-center justify-between rounded-xl border border-white/10 bg-white/[0.03] p-4">
                            <span class="text-sm text-slate-400">Status</span>
                            <span class="font-bold text-emerald-300">Lunas</span>
                        </div>

                        <div class="flex items-center justify-between rounded-xl border border-white/10 bg-white/[0.03] p-4">
                            <span class="text-sm text-slate-400">Tiket diterbitkan</span>
                            <span class="font-bold text-white">{{ $order->tickets->count() }}</span>
                        </div>

                        <a href="{{ route('tickets.index') }}"
                           class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-5 py-4 font-extrabold text-white transition hover:brightness-110">
                            🎫 Lihat Tiket Saya →
                        </a>
                    </div>
                </section>

            {{-- REFUND NEEDED --}}
            @elseif($order->status === 'refund_needed')
                <section class="rounded-2xl border border-red-400/20 bg-slate-900 p-5">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-400/10 text-2xl">!</div>
                    <h2 class="mt-4 text-lg font-extrabold text-red-300">
                        Pembayaran Perlu Ditindaklanjuti
                    </h2>
                    <p class="mt-3 text-sm leading-6 text-slate-300">
                        Pembayaran masuk setelah waktu pesanan habis dan tiket sudah terjual.
                    </p>
                    <p class="mt-2 text-sm leading-6 text-slate-400">
                        Dana perlu ditangani sesuai prosedur pengembalian. Hubungi panitia dengan menyebut kode pesanan di atas.
                    </p>
                </section>

            {{-- OTHER INACTIVE STATUSES --}}
            @else
                <section class="rounded-2xl border border-white/10 bg-slate-900 p-6 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-800 text-2xl">
                        🎫
                    </div>

                    <h2 class="mt-4 text-lg font-extrabold text-white">
                        Pesanan Tidak Aktif
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-400">
                        Pesanan ini sudah tidak dapat digunakan untuk menyelesaikan pembayaran.
                    </p>

                    @if($event)
                        <a href="{{ route('events.show', $event) }}"
                           class="btn-primary mt-5 w-full">
                            Pesan Tiket Lagi
                        </a>
                    @endif
                </section>
            @endif

            {{-- HELP CARD --}}
            <section class="rounded-2xl border border-white/10 bg-slate-900/70 p-5">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-500/10 text-lg">
                        💬
                    </div>

                    <div>
                        <h3 class="font-bold text-white">Butuh bantuan?</h3>
                        <p class="mt-1 text-sm leading-6 text-slate-400">
                            Siapkan kode pesanan saat menghubungi panitia jika mengalami kendala pembayaran.
                        </p>
                    </div>
                </div>
            </section>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Salin kode pesanan
    const copyButton = document.getElementById('copy-order-code');

    if (copyButton) {
        copyButton.addEventListener('click', async function () {
            const code = @json($order->code);

            try {
                await navigator.clipboard.writeText(code);
                copyButton.textContent = 'Tersalin ✓';
            } catch (error) {
                window.prompt('Salin kode pesanan berikut:', code);
            }

            setTimeout(() => {
                copyButton.textContent = 'Salin kode';
            }, 2000);
        });
    }
</script>

@if($order->isPending())
    @if($driver === 'midtrans' && $snapToken)
        <script src="{{ $snapUrl }}" data-client-key="{{ $clientKey }}"></script>
        <script>
            const payButton = document.getElementById('pay-button');

            if (payButton) {
                payButton.addEventListener('click', function () {
                    if (!window.snap) {
                        alert('Layanan pembayaran belum siap. Muat ulang halaman dan coba lagi.');
                        return;
                    }

                    window.snap.pay(@json($snapToken), {
                        onSuccess: function () {
                            location.reload();
                        },
                        onPending: function () {
                            location.reload();
                        },
                        onError: function () {
                            alert('Pembayaran gagal. Silakan coba lagi.');
                        },
                        onClose: function () {
                            // Pengguna menutup popup; status tetap ditentukan oleh server.
                        }
                    });
                });
            }
        </script>
    @endif

    <script>
        // Countdown menggunakan waktu kedaluwarsa dari server.
        const expiresAt = new Date(
            @json($order->expires_at->toIso8601String())
        ).getTime();

        const countdownEl = document.getElementById('countdown');
        const countdownMessage = document.getElementById('countdown-message');

        function tick() {
            if (!countdownEl) return;

            const left = Math.max(
                0,
                Math.floor((expiresAt - Date.now()) / 1000)
            );

            const m = String(Math.floor(left / 60)).padStart(2, '0');
            const s = String(left % 60).padStart(2, '0');

            countdownEl.textContent = left > 0
                ? m + ':' + s
                : 'WAKTU HABIS';

            if (countdownMessage) {
                countdownMessage.textContent = left > 0
                    ? 'Selesaikan transaksi sebelum batas waktu berakhir.'
                    : 'Memeriksa status pesanan...';
            }
        }

        tick();
        setInterval(tick, 1000);

        // Status pembayaran selalu diverifikasi melalui server.
        const statusUrl = @json(route('orders.status', $order));

        async function poll() {
            try {
                const res = await fetch(statusUrl, {
                    headers: { 'Accept': 'application/json' },
                    cache: 'no-store'
                });

                if (!res.ok) {
                    throw new Error('Gagal memeriksa status pesanan.');
                }

                const data = await res.json();

                if (data.status !== 'pending') {
                    location.reload();
                    return;
                }
            } catch (error) {
                // Jika jaringan bermasalah, coba lagi.
            }

            setTimeout(poll, 5000);
        }

        setTimeout(poll, 5000);
    </script>
@endif
@endpush
