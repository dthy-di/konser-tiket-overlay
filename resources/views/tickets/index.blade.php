@extends('layouts.app')

@section('title', 'Tiket Saya')

@section('content')
<h1 class="mb-6 text-3xl font-extrabold">Tiket Saya</h1>

@php
    $labels = [
        'active' => ['Aktif', 'bg-emerald-500/20 text-emerald-300'],
        'used'   => ['Sudah dipakai', 'bg-slate-500/20 text-slate-300'],
        'void'   => ['Dibatalkan', 'bg-red-500/20 text-red-300'],
    ];
@endphp

@forelse($tickets as $ticket)
    @php [$label, $badge] = $labels[$ticket->status] ?? [$ticket->status, 'bg-slate-700 text-slate-200']; @endphp

    <a href="{{ route('tickets.show', $ticket) }}" class="card mb-3 flex items-center justify-between gap-3 transition hover:border-fuchsia-600">
        <div>
            <div class="font-bold">{{ $ticket->event->name }}</div>
            <div class="text-sm text-slate-400">
                {{ $ticket->ticketType->name }} · {{ $ticket->event->starts_at->translatedFormat('d M Y, H:i') }}
            </div>
        </div>
        <span class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold {{ $badge }}">{{ $label }}</span>
    </a>
@empty
    <p class="text-slate-400">
        Belum ada tiket. <a href="{{ route('home') }}" class="text-fuchsia-400 hover:underline">Cari konser</a>
    </p>
@endforelse
@endsection
