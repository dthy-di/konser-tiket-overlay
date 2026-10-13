
@extends('layouts.app')

@section('title', 'Konser Mendatang')

@section('content')
<div class="mx-auto max-w-7xl space-y-8 pb-10">

    {{-- HERO SECTION --}}
    <section class="relative overflow-hidden rounded-3xl border border-white/10 bg-slate-950 px-6 py-10 shadow-2xl sm:px-10 sm:py-14">
        <div class="pointer-events-none absolute -right-16 -top-24 h-80 w-80 rounded-full bg-fuchsia-600/15 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 left-1/3 h-64 w-64 rounded-full bg-indigo-600/15 blur-3xl"></div>

        <div class="relative max-w-3xl">
            <span class="inline-flex items-center gap-2 rounded-full border border-fuchsia-400/20 bg-fuchsia-500/10 px-4 py-2 text-xs font-bold uppercase tracking-[0.25em] text-fuchsia-300">
                <span class="h-2 w-2 rounded-full bg-fuchsia-400"></span>
                Live Music Experience
            </span>

            <h1 class="mt-6 text-4xl font-black leading-tight text-white sm:text-5xl lg:text-6xl">
                Temukan Konser
                <span class="bg-gradient-to-r from-fuchsia-300 via-purple-300 to-indigo-300 bg-clip-text text-transparent">
                    Favoritmu.
                </span>
            </h1>

            <p class="mt-5 max-w-2xl text-sm leading-7 text-slate-400 sm:text-base">
                Pilih acara yang ingin kamu kunjungi, temukan kategori tiket yang sesuai, dan bersiaplah menciptakan momen yang tidak terlupakan.
            </p>

            <div class="mt-7 flex flex-wrap gap-3 text-sm text-slate-300">
                <span class="rounded-xl border border-white/10 bg-white/5 px-4 py-3">🎵 Pengalaman live music</span>
                <span class="rounded-xl border border-white/10 bg-white/5 px-4 py-3">🎫 Tiket digital</span>
            </div>
        </div>
    </section>

    {{-- SECTION HEADER --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.25em] text-fuchsia-400">
                Explore Events
            </p>
            <h2 class="mt-2 text-2xl font-black text-white sm:text-3xl">
                Konser Mendatang
            </h2>
            <p class="mt-2 text-sm text-slate-400">
                Pilih konser berikutnya dan amankan tiketmu.
            </p>
        </div>

        <div class="flex w-fit items-center gap-2 rounded-full border border-white/10 bg-white/[0.03] px-4 py-2 text-xs font-semibold text-slate-300">
            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
            Acara pilihan
        </div>
    </div>

    {{-- EVENT GRID --}}
    @forelse($events as $event)
        @php
            $remaining = $event->ticketTypes->sum(fn ($type) => $type->remaining());
            $minPrice = $event->ticketTypes->min('price');
        @endphp

        <a href="{{ route('events.show', $event) }}"
           class="group relative block overflow-hidden rounded-3xl border border-white/10 bg-slate-900 shadow-xl transition duration-300 hover:-translate-y-1 hover:border-fuchsia-500/50 hover:shadow-2xl hover:shadow-fuchsia-950/20">

            <div class="grid md:grid-cols-5">

                {{-- POSTER --}}
                <div class="relative min-h-[230px] overflow-hidden md:col-span-2 md:min-h-[330px]">
                    @if($event->poster_url)
                        <img src="{{ $event->poster_url }}"
                             alt="{{ $event->name }}"
                             loading="lazy"
                             class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-105">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/10 to-transparent md:bg-gradient-to-r md:from-transparent md:via-slate-950/10 md:to-slate-900"></div>
                    @else
                        <div class="absolute inset-0 bg-gradient-to-br from-fuchsia-700 via-purple-800 to-indigo-950"></div>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <div class="text-6xl transition duration-300 group-hover:scale-110">🎵</div>
                            <p class="mt-4 text-xs font-bold uppercase tracking-[0.3em] text-white/60">
                                Live Experience
                            </p>
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/50 to-transparent"></div>
                    @endif

                    <div class="absolute left-5 top-5">
                        @if($remaining > 0)
                            <span class="inline-flex items-center gap-2 rounded-full border border-emerald-300/20 bg-emerald-950/70 px-3 py-2 text-xs font-bold text-emerald-300 backdrop-blur-md">
                                <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                                Tiket tersedia
                            </span>
                        @else
                            <span class="inline-flex items-center gap-2 rounded-full border border-red-300/20 bg-red-950/70 px-3 py-2 text-xs font-bold text-red-300 backdrop-blur-md">
                                <span class="h-2 w-2 rounded-full bg-red-400"></span>
                                Sold out
                            </span>
                        @endif
                    </div>

                    <div class="absolute bottom-5 left-5 right-5 md:hidden">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-white/70">
                            Upcoming Event
                        </p>
                        <h3 class="mt-1 text-2xl font-black text-white">
                            {{ $event->name }}
                        </h3>
                    </div>
                </div>

                {{-- EVENT DETAILS --}}
                <div class="flex flex-col justify-between gap-6 p-5 sm:p-7 md:col-span-3 md:p-9">

                    <div>
                        <p class="hidden text-xs font-bold uppercase tracking-[0.25em] text-fuchsia-400 md:block">
                            Upcoming Event
                        </p>

                        <h3 class="mt-1 hidden text-2xl font-black leading-tight text-white transition group-hover:text-fuchsia-200 md:block lg:text-3xl">
                            {{ $event->name }}
                        </h3>

                        <div class="mt-3 space-y-3">
                            <div class="flex items-start gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-white/10 bg-white/5">
                                    📅
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                                        Tanggal & Waktu
                                    </p>
                                    <p class="mt-1 text-sm font-semibold text-slate-200">
                                        {{ $event->starts_at->translatedFormat('l, d F Y') }}
                                    </p>
                                    <p class="mt-1 text-sm text-slate-400">
                                        Pukul {{ $event->starts_at->translatedFormat('H:i') }} WIB
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-white/10 bg-white/5">
                                    📍
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                                        Lokasi Acara
                                    </p>
                                    <p class="mt-1 text-sm font-semibold text-slate-200">
                                        {{ $event->venue }}
                                    </p>
                                    @if($event->city)
                                        <p class="mt-1 text-sm text-slate-400">{{ $event->city }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- PRICE AND AVAILABILITY --}}
                    <div class="border-t border-white/10 pt-5">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Harga tiket
                                </p>

                                @if($minPrice !== null)
                                    <p class="mt-1 text-2xl font-black text-white sm:text-3xl">
                                        <span class="text-sm font-medium text-slate-400">Mulai</span>
                                        @rupiah($minPrice)
                                    </p>
                                @else
                                    <p class="mt-1 text-sm font-semibold text-slate-400">
                                        Harga belum tersedia
                                    </p>
                                @endif

                                @if($remaining > 0)
                                    <p class="mt-2 text-xs text-emerald-300">
                                        {{ number_format($remaining, 0, ',', '.') }} tiket tersisa
                                    </p>
                                @else
                                    <p class="mt-2 text-xs font-bold text-red-300">
                                        Semua tiket telah habis
                                    </p>
                                @endif
                            </div>

                            <span class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-fuchsia-600 to-indigo-600 px-5 py-3 font-bold text-white shadow-lg shadow-fuchsia-950/20 transition group-hover:brightness-110">
                                {{ $remaining > 0 ? 'Lihat Tiket' : 'Lihat Detail' }}
                                <span class="transition-transform group-hover:translate-x-1">→</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    @empty
        {{-- EMPTY STATE --}}
        <div class="overflow-hidden rounded-3xl border border-white/10 bg-slate-900 px-6 py-14 text-center sm:py-20">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl border border-fuchsia-400/20 bg-fuchsia-500/10 text-4xl">
                🎵
            </div>

            <h3 class="mt-6 text-xl font-extrabold text-white sm:text-2xl">
                Belum Ada Konser
            </h3>

            <p class="mx-auto mt-3 max-w-md text-sm leading-7 text-slate-400">
                Saat ini belum ada konser yang dijual. Silakan kembali lagi nanti untuk melihat acara terbaru.
            </p>
        </div>
    @endforelse

    {{-- FOOTER NOTE --}}
    @if($events->count() > 0)
        <div class="flex items-start justify-center gap-3 rounded-2xl border border-white/10 bg-white/[0.02] p-5 text-center">
            <span class="text-lg">✨</span>
            <p class="text-xs leading-6 text-slate-400">
                Pilih acara favoritmu untuk melihat detail kategori tiket, harga, dan informasi pemesanan.
            </p>
        </div>
    @endif
</div>
@endsection
