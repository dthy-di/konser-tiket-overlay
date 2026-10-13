
@extends('layouts.app')

@section('title', 'Scanner Tiket')

@section('content')
<div class="mx-auto max-w-5xl space-y-6 pb-10">

    {{-- HEADER --}}
    <div class="relative overflow-hidden rounded-3xl border border-white/10 bg-slate-900 p-6 sm:p-8">
        <div class="absolute -right-16 -top-20 h-56 w-56 rounded-full bg-fuchsia-600/15 blur-3xl"></div>
        <div class="absolute -bottom-20 left-1/3 h-48 w-48 rounded-full bg-indigo-600/15 blur-3xl"></div>

        <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border border-fuchsia-400/20 bg-fuchsia-400/10 px-3 py-1.5 text-xs font-bold uppercase tracking-widest text-fuchsia-300">
                    <span class="h-2 w-2 rounded-full bg-fuchsia-400"></span>
                    Event Check-in
                </div>

                <h1 class="mt-4 text-3xl font-black text-white sm:text-4xl">
                    Scanner Tiket
                </h1>

                <p class="mt-3 max-w-xl text-sm leading-6 text-slate-400">
                    Pindai QR tiket pengunjung untuk memvalidasi tiket sebelum masuk ke area konser.
                </p>
            </div>

            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-3xl shadow-lg">
                🎫
            </div>
        </div>
    </div>

    <div class="grid items-start gap-6 lg:grid-cols-5">

        {{-- SCANNER --}}
        <section class="space-y-5 lg:col-span-3">

            <div class="overflow-hidden rounded-2xl border border-white/10 bg-slate-900 shadow-xl">
                <div class="border-b border-white/10 p-5 sm:p-6">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-fuchsia-400">
                        Scan Station
                    </p>
                    <h2 class="mt-1 text-lg font-extrabold text-white">
                        Pindai QR Tiket
                    </h2>
                    <p class="mt-2 text-sm leading-6 text-slate-400">
                        Pilih acara yang benar, lalu arahkan kamera ke QR tiket pengunjung.
                    </p>
                </div>

                <div class="space-y-5 p-5 sm:p-6">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-300" for="event">
                            Event yang di-scan
                        </label>

                        <select id="event" class="input w-full">
                            @foreach($events as $event)
                                <option value="{{ $event->id }}" @selected($default && $default->id === $event->id)>
                                    {{ $event->name }} · {{ $event->starts_at->translatedFormat('d M Y') }}
                                </option>
                            @endforeach
                        </select>

                        <p class="mt-2 text-xs text-slate-500">
                            Pastikan acara yang dipilih sesuai dengan tiket pengunjung.
                        </p>
                    </div>

                    <div class="relative overflow-hidden rounded-2xl border border-white/10 bg-black">
                        <div id="reader" class="min-h-[260px] overflow-hidden"></div>

                        <div id="camera-placeholder" class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center bg-slate-950 p-6 text-center">
                            <div class="flex h-20 w-20 items-center justify-center rounded-2xl border border-fuchsia-400/20 bg-fuchsia-500/10 text-4xl">
                                ▦
                            </div>
                            <p class="mt-4 font-bold text-white">Kamera belum aktif</p>
                            <p class="mt-2 max-w-xs text-xs leading-5 text-slate-400">
                                Tekan tombol di bawah untuk membuka kamera dan mulai memindai tiket.
                            </p>
                        </div>
                    </div>

                    <button id="start-btn"
                            type="button"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-fuchsia-600 to-indigo-600 px-5 py-4 font-extrabold text-white shadow-lg shadow-fuchsia-950/30 transition hover:brightness-110 active:scale-[0.99]">
                        <span id="camera-icon">📷</span>
                        <span id="camera-label">Buka Kamera &amp; Mulai Scan</span>
                    </button>

                    <div class="flex items-start gap-3 rounded-xl border border-amber-400/20 bg-amber-400/5 p-4">
                        <span class="text-lg">💡</span>
                        <p class="text-xs leading-5 text-slate-400">
                            Kamera memerlukan HTTPS atau localhost. Izinkan akses kamera ketika diminta browser. Jika kamera tidak tersedia, gunakan pemeriksaan kode tiket manual.
                        </p>
                    </div>
                </div>
            </div>

            {{-- MANUAL CHECK --}}
            <div class="rounded-2xl border border-white/10 bg-slate-900 p-5 sm:p-6">
                <div class="flex items-start gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-500/10 text-xl">
                        ⌨️
                    </div>
                    <div>
                        <h2 class="font-extrabold text-white">Pemeriksaan Manual</h2>
                        <p class="mt-1 text-sm leading-6 text-slate-400">
                            Masukkan kode tiket jika QR tidak dapat dipindai.
                        </p>
                    </div>
                </div>

                <form id="manual-form" class="mt-5 flex flex-col gap-3 sm:flex-row">
                    <input id="manual-input"
                           class="input min-w-0 flex-1 font-mono uppercase"
                           placeholder="Masukkan kode tiket"
                           autocomplete="off"
                           autocapitalize="characters">

                    <button type="submit" class="btn-ghost w-full sm:w-auto">
                        Periksa Tiket
                    </button>
                </form>
            </div>

        </section>

        {{-- RESULTS AND HISTORY --}}
        <aside class="space-y-5 lg:col-span-2">

            {{-- RESULT --}}
            <section class="overflow-hidden rounded-2xl border border-white/10 bg-slate-900">
                <div class="border-b border-white/10 p-5">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-fuchsia-400">
                        Verification
                    </p>
                    <h2 class="mt-1 text-lg font-extrabold text-white">
                        Hasil Pemeriksaan
                    </h2>
                </div>

                <div class="p-5">
                    <div id="result" aria-live="polite" class="hidden rounded-2xl p-5 text-center text-white"></div>

                    <div id="result-placeholder" class="py-7 text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-3xl">
                            ✓
                        </div>
                        <p class="mt-4 font-bold text-white">Menunggu pemindaian</p>
                        <p class="mx-auto mt-2 max-w-xs text-sm leading-6 text-slate-400">
                            Hasil validasi tiket akan muncul di sini setelah QR atau kode diperiksa.
                        </p>
                    </div>
                </div>
            </section>

            {{-- SCAN HISTORY --}}
            <section class="overflow-hidden rounded-2xl border border-white/10 bg-slate-900">
                <div class="flex items-center justify-between gap-3 border-b border-white/10 p-5">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-fuchsia-400">
                            Recent Activity
                        </p>
                        <h2 class="mt-1 text-lg font-extrabold text-white">
                            Riwayat Scan
                        </h2>
                    </div>

                    <span id="history-count" class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs font-bold text-slate-300">
                        0
                    </span>
                </div>

                <div class="p-5">
                    <ul id="history" class="space-y-3 text-sm text-slate-400">
                        <li id="history-empty" class="py-5 text-center">
                            <div class="text-2xl">📋</div>
                            <p class="mt-2">Belum ada riwayat scan.</p>
                        </li>
                    </ul>

                    <p class="mt-4 border-t border-white/10 pt-4 text-xs leading-5 text-slate-500">
                        Riwayat ini hanya disimpan selama halaman scanner masih terbuka.
                    </p>
                </div>
            </section>

            {{-- SCAN TIPS --}}
            <section class="rounded-2xl border border-fuchsia-500/20 bg-gradient-to-br from-fuchsia-950/30 to-indigo-950/30 p-5">
                <h3 class="font-bold text-white">Panduan Panitia</h3>
                <ul class="mt-3 space-y-2 text-sm leading-6 text-slate-300">
                    <li>• Periksa nama acara sebelum memindai.</li>
                    <li>• Tiket valid dapat digunakan sesuai hasil server.</li>
                    <li>• Tiket yang sudah digunakan tidak boleh dipakai kembali.</li>
                    <li>• Jika hasil meragukan, periksa ulang dengan koneksi stabil.</li>
                </ul>
            </section>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrf = csrfMeta ? csrfMeta.content : '';

    const checkUrl = @json(route('scanner.check'));
    const eventSelect = document.getElementById('event');
    const resultBox = document.getElementById('result');
    const resultPlaceholder = document.getElementById('result-placeholder');
    const historyList = document.getElementById('history');
    const historyCount = document.getElementById('history-count');
    const historyEmpty = document.getElementById('history-empty');
    const startBtn = document.getElementById('start-btn');
    const cameraLabel = document.getElementById('camera-label');
    const cameraIcon = document.getElementById('camera-icon');
    const cameraPlaceholder = document.getElementById('camera-placeholder');
    const manualInput = document.getElementById('manual-input');

    const styles = {
        valid: {
            bg: 'bg-emerald-600',
            title: '✔ TIKET VALID',
            subtitle: 'Silakan masuk'
        },
        duplicate: {
            bg: 'bg-red-600',
            title: '✖ SUDAH DIGUNAKAN',
            subtitle: 'Tiket tidak dapat digunakan kembali'
        },
        invalid: {
            bg: 'bg-red-600',
            title: '✖ TIKET TIDAK VALID',
            subtitle: 'Periksa kembali kode tiket'
        },
        wrong_event: {
            bg: 'bg-amber-500',
            title: '⚠ SALAH EVENT',
            subtitle: 'Tiket bukan untuk acara yang dipilih'
        },
        void: {
            bg: 'bg-red-600',
            title: '✖ TIKET DIBATALKAN',
            subtitle: 'Tiket tidak dapat digunakan'
        }
    };

    let busy = false;
    let lastPayload = null;
    let lastAt = 0;
    let running = false;
    let reader = null;
    let scanCount = 0;

    function line(parent, text, cls) {
        const el = document.createElement('div');
        el.className = cls || '';
        el.textContent = text;
        parent.appendChild(el);
        return el;
    }

    function show(data) {
        const style = styles[data.result] || styles.invalid;

        resultBox.className = 'rounded-2xl p-5 text-center text-white ' + style.bg;
        resultBox.replaceChildren();

        line(resultBox, style.title, 'text-xl font-black sm:text-2xl');
        line(resultBox, data.message || style.subtitle, 'mt-2 text-sm leading-6');

        if (data.ticket) {
            line(resultBox, data.ticket.type || 'Tiket', 'mt-4 text-lg font-bold');
            line(resultBox, data.ticket.holder || '-', 'mt-1 text-sm');
            line(resultBox, data.ticket.code || '', 'mt-2 break-all font-mono text-xs opacity-80');

            if (data.result === 'duplicate' && data.ticket.used_at) {
                line(resultBox, 'Digunakan pada ' + data.ticket.used_at, 'mt-3 text-xs font-semibold');
            }
        }

        resultPlaceholder.classList.add('hidden');
        resultBox.classList.remove('hidden');

        const li = document.createElement('li');
        li.className = 'flex items-start gap-3 rounded-xl border border-white/10 bg-white/[0.03] p-3';

        const icon = document.createElement('div');
        icon.className = 'flex h-9 w-9 shrink-0 items-center justify-center rounded-lg ' +
            (data.result === 'valid'
                ? 'bg-emerald-500/10 text-emerald-300'
                : 'bg-red-500/10 text-red-300');

        icon.textContent = data.result === 'valid' ? '✓' : '!';
        li.appendChild(icon);

        const details = document.createElement('div');
        details.className = 'min-w-0 flex-1';

        line(details, data.ticket?.holder || style.title, 'font-bold text-white');
        line(details, new Date().toLocaleTimeString('id-ID') + ' · ' + (data.ticket?.code || data.message || ''), 'mt-1 break-words text-xs text-slate-400');

        li.appendChild(details);
        historyList.prepend(li);

        scanCount++;
        historyCount.textContent = String(scanCount);

        if (historyEmpty) {
            historyEmpty.classList.add('hidden');
        }

        while (historyList.children.length > 10) {
            historyList.lastElementChild.remove();
        }

        if (navigator.vibrate) {
            navigator.vibrate(data.result === 'valid' ? 80 : [150, 80, 150]);
        }
    }

    async function check(payload) {
        payload = (payload || '').trim();

        if (!payload || busy) return;

        if (payload === lastPayload && Date.now() - lastAt < 5000) return;

        busy = true;
        lastPayload = payload;
        lastAt = Date.now();

        try {
            const res = await fetch(checkUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({
                    payload: payload,
                    event_id: eventSelect.value
                })
            });

            if (!res.ok) {
                throw new Error('HTTP ' + res.status);
            }

            show(await res.json());
        } catch (e) {
            show({
                result: 'invalid',
                message: 'Gagal terhubung ke server. Periksa koneksi lalu coba lagi.'
            });

            lastPayload = null;
        } finally {
            setTimeout(function () {
                busy = false;
            }, 1200);
        }
    }

    // Kamera scanner
    if (typeof Html5Qrcode !== 'undefined') {
        reader = new Html5Qrcode('reader');
    } else {
        startBtn.disabled = true;
        cameraLabel.textContent = 'Library kamera gagal dimuat';
    }

    startBtn.addEventListener('click', async function () {
        if (!reader) return;

        if (running) {
            try {
                await reader.stop();
                running = false;
                cameraPlaceholder.classList.remove('hidden');
                cameraLabel.textContent = 'Buka Kamera & Mulai Scan';
                cameraIcon.textContent = '📷';
            } catch (e) {
                alert('Kamera gagal dihentikan. Silakan muat ulang halaman jika masalah berlanjut.');
            }
            return;
        }

        if (!eventSelect.value) {
            alert('Pilih event terlebih dahulu.');
            return;
        }

        try {
            cameraLabel.textContent = 'Mengaktifkan kamera...';
            startBtn.disabled = true;

            await reader.start(
                { facingMode: 'environment' },
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1
                },
                function (text) {
                    check(text);
                },
                function () {
                    // Frame yang belum terbaca adalah kondisi normal.
                }
            );

            running = true;
            cameraPlaceholder.classList.add('hidden');
            cameraLabel.textContent = 'Matikan Kamera';
            cameraIcon.textContent = '⏹';
        } catch (e) {
            alert('Kamera tidak bisa dibuka. Pastikan izin kamera diberikan dan halaman menggunakan HTTPS atau localhost.');
            cameraLabel.textContent = 'Buka Kamera & Mulai Scan';
        } finally {
            startBtn.disabled = false;
        }
    });

    // Input manual
    document.getElementById('manual-form').addEventListener('submit', function (ev) {
        ev.preventDefault();

        if (!eventSelect.value) {
            alert('Pilih event terlebih dahulu.');
            return;
        }

        const value = manualInput.value.trim();

        if (!value) {
            manualInput.focus();
            return;
        }

        check(value);
        manualInput.value = '';
    });

    // Reset hasil deduplikasi saat event berganti.
    eventSelect.addEventListener('change', function () {
        lastPayload = null;
        lastAt = 0;
    });
</script>
@endpush
