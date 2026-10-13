
@extends('layouts.app')

@section('title', $event->name)

@section('content')
<div class="mx-auto max-w-7xl space-y-8 pb-10">

    {{-- HERO EVENT --}}
    <section class="relative overflow-hidden rounded-3xl border border-white/10 bg-slate-900 shadow-2xl">
        <div class="absolute inset-0 bg-gradient-to-br from-fuchsia-900/30 via-transparent to-indigo-900/30 pointer-events-none"></div>

        <div class="relative grid md:grid-cols-2">
            <div class="relative min-h-[260px] md:min-h-[420px]">
                @if($event->poster_url)
                    <img src="{{ $event->poster_url }}"
                         alt="{{ $event->name }}"
                         class="absolute inset-0 h-full w-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/20 to-transparent"></div>
                @else
                    <div class="absolute inset-0 bg-gradient-to-br from-fuchsia-700 via-purple-800 to-indigo-950"></div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center">
                            <div class="text-7xl">🎵</div>
                            <p class="mt-4 text-sm font-bold uppercase tracking-[0.35em] text-white/70">
                                Live Music Experience
                            </p>
                        </div>
                    </div>
                @endif

                <div class="absolute bottom-5 left-5 right-5">
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-black/40 px-4 py-2 text-xs font-bold uppercase tracking-wider text-white backdrop-blur-md">
                        <span class="h-2 w-2 rounded-full bg-fuchsia-400"></span>
                        Event Ticket
                    </span>
                </div>
            </div>

            <div class="flex flex-col justify-center p-6 sm:p-9 md:p-12">
                <p class="text-sm font-bold uppercase tracking-[0.25em] text-fuchsia-400">
                    The Event Experience
                </p>

                <h1 class="mt-4 text-3xl font-black leading-tight text-white sm:text-4xl lg:text-5xl">
                    {{ $event->name }}
                </h1>

                <div class="mt-7 space-y-4">
                    <div class="flex items-start gap-4 rounded-2xl border border-white/10 bg-white/5 p-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-fuchsia-500/15 text-xl">
                            📅
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-slate-400">Tanggal & Waktu</p>
                            <p class="mt-1 font-semibold text-white">
                                {{ $event->starts_at->translatedFormat('l, d F Y') }}
                            </p>
                            <p class="text-sm text-slate-300">
                                Pukul {{ $event->starts_at->translatedFormat('H:i') }} WIB
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 rounded-2xl border border-white/10 bg-white/5 p-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-500/15 text-xl">
                            📍
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-slate-400">Lokasi Acara</p>
                            <p class="mt-1 font-semibold text-white">{{ $event->venue }}</p>
                            @if($event->city)
                                <p class="text-sm text-slate-300">{{ $event->city }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                @if($event->description)
                    <div class="mt-6">
                        <h2 class="font-bold text-white">Tentang Acara</h2>
                        <p class="mt-2 whitespace-pre-line text-sm leading-7 text-slate-300">{{ $event->description }}</p>
                    </div>
                @endif

                <div class="mt-7 flex flex-wrap gap-3 text-xs text-slate-300">
                    <span class="rounded-full border border-white/10 bg-white/5 px-3 py-2">🎫 Pilihan kategori tiket</span>
                    <span class="rounded-full border border-white/10 bg-white/5 px-3 py-2">🔒 Checkout aman</span>
                </div>
            </div>
        </div>
    </section>

    {{-- TICKET CONTENT --}}
    <div class="grid items-start gap-8 lg:grid-cols-5">

        {{-- TICKET INFORMATION --}}
        <section class="space-y-5 lg:col-span-3">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.25em] text-fuchsia-400">
                    Find Your Experience
                </p>
                <h2 class="mt-2 text-2xl font-black text-white sm:text-3xl">
                    Pilih Tiket Favoritmu
                </h2>
                <p class="mt-2 text-sm leading-6 text-slate-400">
                    Pilih kategori tiket dan jumlah yang kamu inginkan.
                    Periksa harga serta ketersediaan sebelum melanjutkan.
                </p>
            </div>

            <form method="POST" action="{{ route('checkout.store', $event) }}" id="ticket-form">
                @csrf

                @if($errors->any())
                    <div class="mb-5 rounded-2xl border border-red-500/30 bg-red-500/10 p-4 text-sm text-red-300">
                        <p class="mb-2 font-bold">Periksa kembali pilihanmu</p>
                        <ul class="list-inside list-disc space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="space-y-4">
                    @foreach($event->ticketTypes as $type)
                        @php
                            $max = min($type->remaining(), config('ticketing.max_per_order'));
                            $open = $type->isOnSale() && $max > 0;
                        @endphp

                        <div class="ticket-card group relative overflow-hidden rounded-2xl border border-white/10 bg-slate-900 transition duration-200 hover:border-fuchsia-500/50 hover:bg-slate-900/80"
                             data-ticket-card>
                            <div class="absolute bottom-0 left-0 top-0 w-1 {{ $open ? 'bg-gradient-to-b from-fuchsia-500 to-indigo-500' : 'bg-slate-700' }}"></div>

                            <div class="p-5 sm:p-6">
                                <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="text-lg font-extrabold text-white">
                                                {{ $type->name }}
                                            </h3>

                                            @if($open)
                                                <span class="rounded-full border border-emerald-400/20 bg-emerald-400/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-emerald-300">
                                                    Tersedia
                                                </span>
                                            @elseif($type->remaining() <= 0)
                                                <span class="rounded-full border border-red-400/20 bg-red-400/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-red-300">
                                                    Sold Out
                                                </span>
                                            @else
                                                <span class="rounded-full border border-amber-400/20 bg-amber-400/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-amber-300">
                                                    Belum tersedia
                                                </span>
                                            @endif
                                        </div>

                                        <p class="mt-2 text-2xl font-black text-fuchsia-300">
                                            @rupiah($type->price)
                                        </p>

                                        @if($type->description)
                                            <p class="mt-2 text-sm leading-6 text-slate-400">
                                                {{ $type->description }}
                                            </p>
                                        @endif

                                        <div class="mt-4 flex flex-wrap gap-x-4 gap-y-2 text-xs text-slate-400">
                                            <span>🎟️ Maks. {{ $max }} tiket per pesanan</span>
                                            @if($type->remaining() <= 0)
                                                <span class="text-red-300">Stok habis</span>
                                            @elseif(! $type->isOnSale())
                                                <span class="text-amber-300">Penjualan belum dibuka / sudah ditutup</span>
                                            @else
                                                <span class="text-emerald-300">Dapat dipilih</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex shrink-0 items-center justify-between gap-4 rounded-xl border border-white/10 bg-slate-950/60 p-3 sm:min-w-[145px] sm:flex-col sm:items-stretch">
                                        <label for="qty-{{ $type->id }}" class="text-xs font-semibold text-slate-400 sm:text-center">
                                            Jumlah tiket
                                        </label>
                                        <input id="qty-{{ $type->id }}"
                                               type="number"
                                               name="qty[{{ $type->id }}]"
                                               value="{{ old('qty.'.$type->id, 0) }}"
                                               min="0"
                                               max="{{ $max }}"
                                               data-price="{{ $type->price }}"
                                               @disabled(! $open)
                                               class="input qty !w-20 text-center font-bold sm:!w-full">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($event->ticketTypes->isEmpty())
                    <div class="rounded-2xl border border-white/10 bg-slate-900 p-8 text-center">
                        <div class="text-4xl">🎫</div>
                        <p class="mt-3 font-bold text-white">Tiket belum tersedia</p>
                        <p class="mt-1 text-sm text-slate-400">Silakan periksa kembali nanti.</p>
                    </div>
                @endif

                <div class="mt-6 rounded-2xl border border-white/10 bg-gradient-to-br from-slate-900 to-indigo-950/50 p-5 sm:p-6">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm text-slate-400">Total pembayaran</p>
                            <p class="mt-1 text-xs text-slate-500">Sesuai jumlah tiket yang dipilih</p>
                        </div>
                        <strong id="total" class="text-2xl font-black text-white sm:text-3xl">
                            Rp 0
                        </strong>
                    </div>

                    <div class="my-5 border-t border-white/10"></div>

                    @auth
                        <button type="submit"
                                id="checkout-button"
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-fuchsia-600 to-indigo-600 px-5 py-4 font-extrabold text-white shadow-lg shadow-fuchsia-950/30 transition hover:brightness-110 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-50">
                            <span>Lanjut ke Pembayaran</span>
                            <span aria-hidden="true">→</span>
                        </button>
                    @else
                        <a href="{{ route('login') }}"
                           class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-fuchsia-600 to-indigo-600 px-5 py-4 text-center font-extrabold text-white shadow-lg shadow-fuchsia-950/30 transition hover:brightness-110">
                            Masuk untuk Membeli →
                        </a>
                    @endauth

                    <p class="mt-4 text-center text-xs leading-5 text-slate-500">
                        Maksimal {{ config('ticketing.max_per_order') }} tiket per pesanan.
                        Tiket ditahan {{ config('ticketing.order_ttl_minutes') }} menit sejak checkout.
                    </p>
                </div>
            </form>
        </section>

        {{-- INFORMATION SIDEBAR --}}
        <aside class="space-y-5 lg:sticky lg:top-6 lg:col-span-2">
            <div class="overflow-hidden rounded-2xl border border-white/10 bg-slate-900">
                <div class="border-b border-white/10 bg-gradient-to-r from-fuchsia-950/70 to-indigo-950/70 p-5">
                    <h2 class="text-lg font-extrabold text-white">Informasi Tiket</h2>
                    <p class="mt-1 text-sm text-slate-400">Hal penting sebelum melakukan pemesanan</p>
                </div>

                <div class="space-y-5 p-5">
                    <div class="flex gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-fuchsia-500/10 text-lg">🎫</div>
                        <div>
                            <h3 class="font-bold text-white">Pilih kategori yang sesuai</h3>
                            <p class="mt-1 text-sm leading-6 text-slate-400">
                                Periksa nama kategori, harga, dan deskripsi tiket sebelum menentukan pilihan.
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-500/10 text-lg">⏳</div>
                        <div>
                            <h3 class="font-bold text-white">Batas waktu pembayaran</h3>
                            <p class="mt-1 text-sm leading-6 text-slate-400">
                                Setelah checkout, tiket ditahan selama
                                {{ config('ticketing.order_ttl_minutes') }} menit.
                                Selesaikan pembayaran sebelum batas waktu berakhir.
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-lg">🔐</div>
                        <div>
                            <h3 class="font-bold text-white">Konfirmasi pembayaran</h3>
                            <p class="mt-1 text-sm leading-6 text-slate-400">
                                Ikuti petunjuk pembayaran yang muncul setelah checkout. Pastikan status pesanan sudah berhasil sebelum menggunakan tiket.
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-lg">📱</div>
                        <div>
                            <h3 class="font-bold text-white">Simpan tiket digital</h3>
                            <p class="mt-1 text-sm leading-6 text-slate-400">
                                Setelah tiket diterbitkan, simpan informasi tiket dan QR check-in untuk ditunjukkan sesuai prosedur panitia.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-fuchsia-500/20 bg-gradient-to-br from-fuchsia-950/30 to-indigo-950/30 p-5">
                <div class="flex items-start gap-3">
                    <span class="text-xl">💡</span>
                    <div>
                        <h3 class="font-bold text-white">Tips sebelum checkout</h3>
                        <ul class="mt-3 space-y-2 text-sm leading-6 text-slate-300">
                            <li>• Pastikan tanggal dan lokasi acara sudah benar.</li>
                            <li>• Periksa kembali jumlah tiket yang dipilih.</li>
                            <li>• Pastikan koneksi internet stabil saat membayar.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-5 text-center">
                <p class="text-sm font-bold text-white">Siap menikmati acaranya?</p>
                <p class="mt-1 text-xs leading-5 text-slate-400">
                    Pilih tiketmu dan lanjutkan ke proses pemesanan.
                </p>
            </div>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const inputs = document.querySelectorAll('.qty');
    const totalEl = document.getElementById('total');
    const form = document.getElementById('ticket-form');
    const checkoutButton = document.getElementById('checkout-button');

    function updateTotal() {
        let total = 0;
        let selected = 0;

        inputs.forEach(input => {
            const max = Number(input.max);
            let quantity = parseInt(input.value, 10) || 0;

            // Batasi nilai tampilan agar tidak melewati batas input.
            quantity = Math.max(0, quantity);

            if (Number.isFinite(max)) {
                quantity = Math.min(quantity, max);
            }

            if (input.value !== String(quantity)) {
                input.value = quantity;
            }

            total += quantity * Number(input.dataset.price);
            selected += quantity;
        });

        totalEl.textContent = 'Rp ' + total.toLocaleString('id-ID');

        if (checkoutButton) {
            checkoutButton.disabled = selected === 0;
        }
    }

    inputs.forEach(input => {
        input.addEventListener('input', updateTotal);
        input.addEventListener('change', updateTotal);
    });

    updateTotal();

    if (form && checkoutButton) {
        form.addEventListener('submit', function () {
            checkoutButton.disabled = true;
            checkoutButton.querySelector('span').textContent = 'Memproses pesanan...';
        });
    }
</script>
@endpush
