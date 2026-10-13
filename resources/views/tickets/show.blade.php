@extends('layouts.app')

@section('title', 'Tiket '.$ticket->event->name)

@section('content')
<div class="mx-auto max-w-sm">
    <a href="{{ route('tickets.index') }}" class="mb-3 inline-block text-sm text-slate-400 hover:text-white">← Semua tiket</a>

    <div class="card text-center">
        <div class="text-sm font-semibold uppercase tracking-widest text-fuchsia-300">{{ $ticket->ticketType->name }}</div>
        <h1 class="mt-1 text-2xl font-extrabold">{{ $ticket->event->name }}</h1>
        <p class="mt-1 text-sm text-slate-400">{{ $ticket->event->starts_at->translatedFormat('l, d F Y · H:i') }} WIB</p>
        <p class="text-sm text-slate-400">{{ $ticket->event->venue }}</p>

        @if($ticket->status === 'active')
            <div class="mx-auto my-5 w-fit rounded-xl bg-white p-3">{!! $qrSvg !!}</div>
            <p class="font-mono text-sm tracking-widest">{{ $ticket->code }}</p>
            <p class="mt-2 text-xs text-slate-500">Tunjukkan QR ini ke petugas di pintu masuk. Jangan dibagikan ke orang lain.</p>
        @elseif($ticket->status === 'used')
            <div class="my-6 rounded-xl bg-slate-800 p-5">
                <p class="font-semibold">Tiket sudah dipakai</p>
                <p class="text-sm text-slate-400">{{ $ticket->used_at?->translatedFormat('d M Y, H:i') }}</p>
            </div>
        @else
            <div class="my-6 rounded-xl bg-red-900/40 p-5 text-red-200">Tiket ini sudah dibatalkan.</div>
        @endif

        <div class="border-t border-dashed border-slate-700 pt-3 text-sm text-slate-400">
            Atas nama <strong class="text-slate-200">{{ $ticket->holder_name }}</strong>
        </div>
    </div>
</div>
@endsection
