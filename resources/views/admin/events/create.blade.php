@extends('layouts.app')

@section('title', 'Event Baru')

@section('content')
<div class="mx-auto max-w-2xl">
    <h1 class="mb-6 text-3xl font-extrabold">Event Baru</h1>

    <form method="POST" action="{{ route('admin.events.store') }}" class="card space-y-4">
        @csrf

        <div>
            <label class="label" for="name">Nama event</label>
            <input class="input" id="name" name="name" value="{{ old('name') }}" required>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="label" for="venue">Venue</label>
                <input class="input" id="venue" name="venue" value="{{ old('venue') }}" required>
            </div>
            <div>
                <label class="label" for="city">Kota</label>
                <input class="input" id="city" name="city" value="{{ old('city') }}">
            </div>
        </div>

        <div>
            <label class="label" for="starts_at">Tanggal &amp; jam mulai (WIB)</label>
            <input class="input" id="starts_at" type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" required>
        </div>

        <div>
            <label class="label" for="poster_url">URL poster (opsional)</label>
            <input class="input" id="poster_url" type="url" name="poster_url" value="{{ old('poster_url') }}" placeholder="https://...">
        </div>

        <div>
            <label class="label" for="description">Deskripsi</label>
            <textarea class="input" id="description" name="description" rows="4">{{ old('description') }}</textarea>
        </div>

        <div>
            <div class="label">Kategori tiket (baris yang kosong diabaikan)</div>
            <div class="space-y-2">
                @for($i = 0; $i < 4; $i++)
                    <div class="grid grid-cols-5 gap-2">
                        <input class="input col-span-2" name="types[{{ $i }}][name]" value="{{ old("types.$i.name") }}" placeholder="Nama (mis. VIP)">
                        <input class="input col-span-2" type="number" min="0" name="types[{{ $i }}][price]" value="{{ old("types.$i.price") }}" placeholder="Harga (Rp)">
                        <input class="input" type="number" min="1" name="types[{{ $i }}][quota]" value="{{ old("types.$i.quota") }}" placeholder="Kuota">
                    </div>
                @endfor
            </div>
        </div>

        <div>
            <label class="label" for="status">Status</label>
            <select class="input" id="status" name="status">
                <option value="draft" @selected(old('status', 'draft') === 'draft')>Draft (belum tampil ke publik)</option>
                <option value="published" @selected(old('status') === 'published')>Langsung dijual</option>
            </select>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary">Simpan Event</button>
            <a href="{{ route('admin.dashboard') }}" class="btn-ghost">Batal</a>
        </div>
    </form>
</div>
@endsection
