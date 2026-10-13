
@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-3xl font-extrabold">Dashboard Admin</h1>
    <a href="{{ route('admin.events.create') }}" class="btn-primary">+ Event Baru</a>
</div>

<div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
    <div class="card">
        <div class="text-xs text-slate-400">Pendapatan (lunas)</div>
        <div class="mt-1 text-lg font-bold">@rupiah($stats['revenue'])</div>
    </div>
    <div class="card">
        <div class="text-xs text-slate-400">Tiket terbit</div>
        <div class="mt-1 text-lg font-bold">{{ number_format($stats['tickets'], 0, ',', '.') }}</div>
    </div>
    <div class="card">
        <div class="text-xs text-slate-400">Pesanan belum dibayar</div>
        <div class="mt-1 text-lg font-bold">{{ number_format($stats['pending'], 0, ',', '.') }}</div>
    </div>
    <div class="card {{ $stats['refund_needed'] > 0 ? 'border-red-600' : '' }}">
        <div class="text-xs text-slate-400">Perlu refund manual</div>
        <div class="mt-1 text-lg font-bold {{ $stats['refund_needed'] > 0 ? 'text-red-400' : '' }}">
            {{ $stats['refund_needed'] }}
        </div>
    </div>
</div>

@forelse($events as $event)
    <div class="card mb-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold">{{ $event->name }}</h2>
                <p class="text-sm text-slate-400">
                    {{ $event->starts_at->translatedFormat('d F Y · H:i') }}
                    · {{ $event->venue }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <form method="POST" action="{{ route('admin.events.toggle', $event) }}"
                      class="flex items-center gap-2">
                    @csrf

                    <span class="rounded-full px-3 py-1 text-xs font-semibold
                        {{ $event->status === 'published'
                            ? 'bg-emerald-500/20 text-emerald-300'
                            : 'bg-slate-500/20 text-slate-300' }}">
                        {{ $event->status === 'published' ? 'Dijual' : 'Draft' }}
                    </span>

                    <button type="submit" class="btn-ghost !px-3 !py-1 text-sm">
                        {{ $event->status === 'published'
                            ? 'Tutup penjualan'
                            : 'Terbitkan' }}
                    </button>
                </form>

                {{-- Tombol hapus event --}}
                <form method="POST"
                      action="{{ route('admin.events.destroy', $event) }}"
                      onsubmit="return confirm('Yakin ingin menghapus event {{ addslashes($event->name) }}? Tindakan ini tidak dapat dibatalkan.');">
                    @csrf
                    @method('DELETE')

                    <button type="submit"
                            class="rounded-lg border border-red-500/40 bg-red-500/10 px-3 py-2 text-sm font-semibold text-red-400 transition hover:bg-red-500/20">
                        Hapus
                    </button>
                </form>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-3 gap-3 text-sm">
            <div>
                <div class="text-slate-400">Tiket terbit</div>
                <div class="font-bold">
                    {{ number_format($event->tickets_issued, 0, ',', '.') }}
                </div>
            </div>
            <div>
                <div class="text-slate-400">Sudah check-in</div>
                <div class="font-bold">
                    {{ number_format($event->tickets_used, 0, ',', '.') }}
                </div>
            </div>
            <div>
                <div class="text-slate-400">Pendapatan</div>
                <div class="font-bold">
                    @rupiah($revenue[$event->id] ?? 0)
                </div>
            </div>
        </div>

        <div class="mt-4 space-y-3">
            @foreach($event->ticketTypes as $type)
                @php
                    $percent = min(
                        100,
                        (int) round($type->sold / max(1, $type->quota) * 100)
                    );
                @endphp

                <div>
                    <div class="flex justify-between text-sm">
                        <span>{{ $type->name }} · @rupiah($type->price)</span>
                        <span class="text-slate-400">
                            {{ number_format($type->sold, 0, ',', '.') }}
                            /
                            {{ number_format($type->quota, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="mt-1 h-2 overflow-hidden rounded-full bg-slate-800">
                        <div class="h-full rounded-full bg-fuchsia-500"
                             style="width: {{ $percent }}%"></div>
                    </div>
                </div>
            @endforeach

            <p class="text-xs text-slate-500">
                Angka "terjual" sudah termasuk tiket yang sedang ditahan pesanan belum dibayar.
            </p>
        </div>
    </div>
@empty
    <p class="text-slate-400">Belum ada event. Klik "+ Event Baru".</p>
@endforelse
@endsection
