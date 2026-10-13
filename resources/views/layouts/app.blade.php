<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tiket Konser') · {{ config('app.name') }}</title>

    {{-- Tailwind lewat CDN: cukup untuk development. Untuk produksi sebaiknya di-compile (lihat README). --}}
    <script src="https://cdn.tailwindcss.com"></script>
    @verbatim
    <style type="text/tailwindcss">
        @layer components {
            .card { @apply rounded-2xl border border-slate-800 bg-slate-900 p-5; }
            .input { @apply w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100 placeholder-slate-500 focus:border-fuchsia-500 focus:outline-none; }
            .label { @apply mb-1 block text-sm font-medium text-slate-300; }
            .btn { @apply inline-flex items-center justify-center rounded-lg px-4 py-2 font-semibold transition; }
            .btn-primary { @apply inline-flex items-center justify-center rounded-lg bg-fuchsia-600 px-4 py-2 font-semibold text-white transition hover:bg-fuchsia-500 disabled:opacity-50; }
            .btn-ghost { @apply inline-flex items-center justify-center rounded-lg border border-slate-700 px-4 py-2 font-semibold text-slate-200 transition hover:bg-slate-800; }
        }
    </style>
    @endverbatim
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 antialiased">

<header class="border-b border-slate-800">
    <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-3">
        <a href="{{ route('home') }}" class="text-lg font-extrabold tracking-tight text-fuchsia-400">🎫 {{ config('app.name') }}</a>

        <nav class="flex items-center gap-4 text-sm">
            @auth
                <a href="{{ route('tickets.index') }}" class="hover:text-fuchsia-300">Tiket Saya</a>
                @if(auth()->user()->hasRole('scanner', 'admin'))
                    <a href="{{ route('scanner.index') }}" class="hover:text-fuchsia-300">Scanner</a>
                @endif
                @if(auth()->user()->hasRole('admin'))
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-fuchsia-300">Admin</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-slate-400 hover:text-white">Keluar</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="hover:text-fuchsia-300">Masuk</a>
                <a href="{{ route('register') }}" class="btn-primary !py-1.5">Daftar</a>
            @endauth
        </nav>
    </div>
</header>

<main class="mx-auto max-w-5xl px-4 py-8">
    @if(session('status'))
        <div class="mb-4 rounded-lg border border-emerald-700 bg-emerald-900/40 px-4 py-3 text-emerald-200">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded-lg border border-red-700 bg-red-900/40 px-4 py-3 text-red-200">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>

@stack('scripts')
</body>
</html>
