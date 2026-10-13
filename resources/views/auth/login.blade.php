
@extends('layouts.app')

@section('title', 'Masuk')

@section('content')
<div class="relative mx-auto flex min-h-[75vh] max-w-6xl items-center justify-center overflow-hidden px-4 py-10">

    {{-- BACKGROUND GLOW --}}
    <div class="pointer-events-none absolute left-1/2 top-1/2 h-96 w-96 -translate-x-1/2 -translate-y-1/2 rounded-full bg-fuchsia-600/10 blur-3xl"></div>
    <div class="pointer-events-none absolute left-10 top-20 h-48 w-48 rounded-full bg-indigo-600/10 blur-3xl"></div>

    <div class="relative grid w-full max-w-5xl overflow-hidden rounded-3xl border border-white/10 bg-slate-950 shadow-2xl shadow-black/40 md:grid-cols-2">

        {{-- LEFT DECORATIVE PANEL --}}
        <div class="relative hidden min-h-[570px] flex-col justify-between overflow-hidden bg-gradient-to-br from-fuchsia-950 via-slate-900 to-indigo-950 p-10 md:flex">

            <div class="absolute -right-20 top-20 h-72 w-72 rounded-full border border-white/10"></div>
            <div class="absolute -right-10 top-32 h-52 w-52 rounded-full border border-white/10"></div>
            <div class="absolute -bottom-28 -left-20 h-80 w-80 rounded-full bg-fuchsia-600/10 blur-2xl"></div>

            <div class="relative">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-fuchsia-400/30 bg-fuchsia-500/10 text-2xl">
                    ✦
                </div>

                <p class="mt-8 text-xs font-bold uppercase tracking-[0.35em] text-fuchsia-300">
                    Your Next Experience
                </p>

                <h2 class="mt-4 text-4xl font-black leading-tight text-white">
                    Musik.
                    <br>
                    Momen.
                    <br>
                    <span class="bg-gradient-to-r from-fuchsia-300 to-indigo-300 bg-clip-text text-transparent">
                        Kenangan.
                    </span>
                </h2>

                <p class="mt-5 max-w-sm text-sm leading-7 text-slate-300">
                    Masuk ke akunmu dan bersiap untuk menemukan pengalaman konser yang tidak terlupakan.
                </p>
            </div>

            <div class="relative rounded-2xl border border-white/10 bg-white/[0.04] p-5 backdrop-blur-sm">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-fuchsia-600 to-indigo-600 text-xl">
                        🎫
                    </div>
                    <div>
                        <p class="font-bold text-white">Semua tiketmu, satu tempat</p>
                        <p class="mt-1 text-xs text-slate-400">Pesan, bayar, dan akses tiket digital.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- LOGIN FORM --}}
        <div class="flex flex-col justify-center p-6 sm:p-10 md:p-12">

            <div class="mb-8 md:hidden">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-fuchsia-400/30 bg-fuchsia-500/10 text-2xl">
                    ✦
                </div>
                <p class="mt-4 text-xs font-bold uppercase tracking-[0.3em] text-fuchsia-400">
                    Concert Ticketing
                </p>
            </div>

            <div>
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-fuchsia-400">
                    Welcome Back
                </p>

                <h1 class="mt-3 text-3xl font-black text-white sm:text-4xl">
                    Selamat Datang
                </h1>

                <p class="mt-3 text-sm leading-6 text-slate-400">
                    Masuk ke akunmu untuk melanjutkan perjalanan menuju acara favorit.
                </p>
            </div>

            @if($errors->any())
                <div class="mt-6 rounded-xl border border-red-500/20 bg-red-500/10 p-4 text-sm text-red-300">
                    <p class="font-bold">Login belum berhasil</p>
                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ url('/login') }}" class="mt-8 space-y-5">
                @csrf

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-300" for="email">
                        Alamat Email
                    </label>

                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-slate-500">
                            ✉
                        </span>

                        <input
                            class="input w-full !rounded-xl !border-white/10 !bg-slate-900/80 py-3.5 pl-11 text-white placeholder:text-slate-600 focus:!border-fuchsia-500"
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="nama@email.com"
                            autocomplete="email"
                            required
                            autofocus>
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-300" for="password">
                        Password
                    </label>

                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-slate-500">
                            🔒
                        </span>

                        <input
                            class="input w-full !rounded-xl !border-white/10 !bg-slate-900/80 py-3.5 pl-11 text-white placeholder:text-slate-600 focus:!border-fuchsia-500"
                            id="password"
                            type="password"
                            name="password"
                            placeholder="Masukkan password"
                            autocomplete="current-password"
                            required>
                    </div>
                </div>

                <label class="flex cursor-pointer items-center gap-3 text-sm text-slate-400">
                    <input
                        type="checkbox"
                        name="remember"
                        value="1"
                        @checked(old('remember'))
                        class="h-4 w-4 rounded border-slate-600 bg-slate-900 text-fuchsia-500 focus:ring-fuchsia-500">
                    <span>Ingat saya</span>
                </label>

                <button
                    type="submit"
                    class="group flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-fuchsia-600 to-indigo-600 px-5 py-4 font-extrabold text-white shadow-lg shadow-fuchsia-950/30 transition duration-200 hover:-translate-y-0.5 hover:brightness-110 active:translate-y-0">
                    <span>Masuk ke Akun</span>
                    <span class="transition-transform group-hover:translate-x-1">→</span>
                </button>
            </form>

            <div class="my-7 flex items-center gap-4">
                <div class="h-px flex-1 bg-white/10"></div>
                <span class="text-xs text-slate-500">PENGGUNA BARU?</span>
                <div class="h-px flex-1 bg-white/10"></div>
            </div>

            <a
                href="{{ route('register') }}"
                class="flex w-full items-center justify-center rounded-xl border border-white/10 bg-white/[0.03] px-5 py-3.5 font-bold text-slate-200 transition hover:border-fuchsia-500/40 hover:bg-fuchsia-500/5 hover:text-white">
                Buat Akun Baru
            </a>

            <p class="mt-8 text-center text-xs leading-5 text-slate-500">
                Dengan masuk, kamu dapat mengelola pesanan dan mengakses tiket digital melalui akunmu.
            </p>
        </div>
    </div>
</div>
@endsection
