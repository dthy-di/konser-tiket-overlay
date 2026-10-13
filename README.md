# Sistem Booking Tiket Konser (Laravel)

Fitur: katalog event, pilih tiket, checkout dengan stok anti-oversell, pembayaran Midtrans
(VA semua bank, QRIS, e-wallet, kartu, minimarket), tiket dengan QR bertanda tangan,
scanner kamera untuk petugas, dan dashboard admin.

> **Status:** kode ini sudah lolos cek sintaks PHP dan logika tanda tangan QR sudah diuji terpisah,
> tapi **belum pernah dijalankan penuh sebagai aplikasi Laravel** (saat dibuat, server Packagist tidak
> bisa diakses). Jalankan langkah 1-6 di bawah, coba alurnya sekali, dan kalau ada error kirim pesan
> error-nya supaya bisa diperbaiki.

---

## 1. Yang perlu diinstall di komputer

| Software | Wajib? | Untuk apa |
|---|---|---|
| **PHP 8.2 atau lebih baru** (disarankan 8.3) | Wajib | Menjalankan aplikasi |
| **Composer** | Wajib | Mengunduh Laravel dan library |
| **Code editor** (VS Code) | Disarankan | Mengedit kode |
| **MySQL / MariaDB** | Hanya untuk produksi | Untuk development cukup SQLite (sudah bawaan Laravel) |
| **Git** | Opsional | Menyimpan versi kode |
| **ngrok** atau **cloudflared** | Opsional | Tes webhook Midtrans dan kamera scanner di HP (butuh HTTPS) |
| **Akun Midtrans Sandbox** (gratis) | Untuk tes pembayaran asli | https://dashboard.sandbox.midtrans.com |

Ekstensi PHP yang harus aktif: `curl`, `mbstring`, `openssl`, `pdo_sqlite` (atau `pdo_mysql`), `xml`,
`xmlwriter`, `fileinfo`, `tokenizer`, `ctype`, `zip`. Cek dengan `php -m`.

**Cara termudah per sistem operasi**
- **Windows:** install **Laragon (Full)**. Satu paket sudah berisi PHP, Composer, MySQL, dan Git.
  Setelah itu buka *Terminal* dari Laragon.
- **macOS:** install **Laravel Herd** (https://herd.laravel.com), atau `brew install php composer`.
- **Linux (Ubuntu/Debian):**
  `sudo apt install php php-cli php-curl php-mbstring php-xml php-sqlite3 php-mysql php-zip unzip composer`

Cek hasil instalasi:
```bash
php -v
composer -V
```

## 2. Buat project Laravel dan tempel paket ini

```bash
composer create-project laravel/laravel konser-tiket
```

Lalu **salin seluruh isi folder paket ini ke dalam folder `konser-tiket`** dan pilih *timpa / replace*
kalau ditanya. File Laravel bawaan yang akan tertimpa hanya 6:
`routes/web.php`, `routes/console.php`, `app/Models/User.php`,
`app/Providers/AppServiceProvider.php`, `database/seeders/DatabaseSeeder.php`,
dan `tests/Feature/ExampleTest.php`.

Install dua library yang dipakai (pembayaran dan pembuat QR):

```bash
cd konser-tiket
composer require midtrans/midtrans-php bacon/bacon-qr-code
```

## 3. Atur file `.env`

Buka file `.env` di folder `konser-tiket`, lalu tempel isi file **`env-tambahan.txt`** di **paling bawah**.
Untuk percobaan pertama biarkan `PAYMENT_DRIVER=fake` (tidak perlu akun Midtrans).

## 4. Buat database dan data contoh

```bash
php artisan migrate --seed
```

Ini membuat semua tabel dan 3 akun contoh plus 1 event dengan kuota 10.000 tiket
(Festival 7.000, VIP 2.500, VVIP 500):

| Peran | Email | Password |
|---|---|---|
| Admin | admin@konser.test | password |
| Petugas scanner | petugas@konser.test | password |
| Pembeli | pembeli@konser.test | password |

## 5. Jalankan

Butuh **dua terminal**:

```bash
# Terminal 1: web server
php artisan serve          # buka http://localhost:8000

# Terminal 2: pelepas stok otomatis (WAJIB, jalan tiap menit)
php artisan schedule:work
```

Terminal 2 yang mengembalikan stok dari pesanan yang tidak dibayar. Kalau tidak jalan, tiket yang
sudah ditahan tidak akan pernah kembali ke penjualan.

### Tes otomatis (disarankan dijalankan sekali)

```bash
php artisan test
```

Ada 6 tes untuk bagian paling kritis: stok tidak bisa melebihi kuota, pembayaran/webhook yang
terkirim dua kali tidak membuat tiket ganda, signature webhook palsu ditolak, pesanan kedaluwarsa
mengembalikan stok, dan tiket hanya bisa di-scan sekali. Kalau semuanya hijau, fondasinya sehat.

## 6. Coba alurnya (mode demo)

1. Login sebagai **pembeli** lalu pilih konser dan beli tiket, lalu klik **Simulasi: Bayar Sekarang**.
2. Buka **Tiket Saya**: tiket dengan QR sudah terbit.
3. Di browser lain (atau jendela incognito) login sebagai **petugas**, buka **Scanner**.
   - Arahkan kamera ke QR di layar, atau ketik kode 20 karakter di bawah QR ke kolom manual.
   - Scan pertama = hijau **VALID**. Scan kedua = merah **SUDAH DIPAKAI**.
4. Login sebagai **admin** untuk melihat penjualan, check-in, dan membuat event baru.

> Kamera browser hanya jalan di `http://localhost` atau **HTTPS**. Untuk tes dari HP, buka tunnel
> (`ngrok http 8000`) lalu pakai alamat https dari ngrok di HP.

---

## Pakai pembayaran asli (Midtrans)

1. Daftar di https://dashboard.sandbox.midtrans.com, lalu **Settings → Access Keys**, salin Server Key dan Client Key.
2. Isi di `.env`:
   ```
   PAYMENT_DRIVER=midtrans
   MIDTRANS_SERVER_KEY=SB-Mid-server-xxxx
   MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxx
   MIDTRANS_IS_PRODUCTION=false
   ```
3. Di dashboard Midtrans: **Settings → Snap Preferences** lalu aktifkan metode yang diinginkan
   (BCA/BNI/BRI/Mandiri/Permata/CIMB VA, QRIS, GoPay, ShopeePay, Alfamart/Indomaret, kartu).
4. Di **Settings → Configuration → Payment Notification URL** isi:
   `https://DOMAIN-KAMU/api/webhooks/midtrans`
   (saat tes lokal pakai alamat ngrok, mis. `https://abcd.ngrok-free.app/api/webhooks/midtrans`).
5. Bayar pakai simulator Midtrans: https://simulator.sandbox.midtrans.com

Webhook tidak wajib saat tes lokal. Halaman pesanan juga menanyakan status ke Midtrans tiap beberapa
detik, jadi tiket tetap terbit tanpa tunnel. Untuk produksi, webhook tetap wajib diaktifkan.

Kalau pembayaran gagal dibuat, lihat pesan error di `storage/logs/laravel.log`.

---

## Cara kerja bagian pentingnya

- **Anti-oversell:** stok ditahan dengan satu query atomik
  `UPDATE ticket_types SET sold = sold + N WHERE id = ? AND sold + N <= quota`.
  Database yang menjamin kuota tidak terlewati, seramai apa pun yang klik bersamaan.
- **Tahan stok 15 menit:** pesanan `pending` menahan stok. Scheduler `orders:expire` melepasnya
  kalau tidak dibayar (atur lewat `ORDER_TTL_MINUTES`).
- **Webhook aman:** signature SHA512 diverifikasi, nominal dicocokkan dengan pesanan, dan semua
  proses idempotent. Notifikasi yang terkirim dua kali tidak membuat tiket ganda.
- **Bayar terlambat:** kalau uang masuk setelah pesanan kedaluwarsa, sistem mencoba mengambil stok
  lagi. Kalau sudah habis, pesanan ditandai **Perlu refund** di dashboard admin.
- **QR tiket:** isi QR `TKT-{kode}-{HMAC}`. Tanda tangan dibuat dari `APP_KEY`, jadi QR palsu
  ditolak. Kode scan hanya bisa berubah `active → used` sekali (race-safe).
- **Scan log:** setiap percobaan scan (valid, duplikat, palsu, salah event) tercatat di tabel `scan_logs`.

## Sebelum dipakai sungguhan (produksi)

- [ ] `APP_ENV=production`, `APP_DEBUG=false`, pakai **HTTPS**.
- [ ] Database **MySQL/PostgreSQL**, bukan SQLite.
- [ ] `PAYMENT_DRIVER=midtrans` dengan **key production** dan `MIDTRANS_IS_PRODUCTION=true`
      (butuh akun Midtrans yang sudah diverifikasi).
- [ ] Jalankan seeder **tidak** di production. Buat akun admin lewat `php artisan tinker`.
- [ ] Pasang cron: `* * * * * cd /path/konser-tiket && php artisan schedule:run >> /dev/null 2>&1`
- [ ] Ganti Tailwind CDN dengan build Vite (`npm install && npm run build`).
- [ ] `php artisan config:cache && php artisan route:cache && php artisan view:cache`
- [ ] Backup database otomatis. **Jangan ubah `APP_KEY`** setelah tiket terbit (QR lama jadi tidak valid).
- [ ] Untuk "war tiket" ribuan orang bersamaan: pakai Redis (cache/session/queue), beberapa server
      di belakang load balancer, dan pertimbangkan waiting room / antrean sebelum halaman beli.
- [ ] Tes beban sebelum hari-H (mis. k6 atau JMeter).

## Pengembangan lanjutan yang mudah ditambahkan

Pilih nomor kursi, kirim e-tiket via email/WhatsApp, PDF tiket, scanner mode offline,
batas pembelian per akun per event, kode promo, dan laporan penjualan per hari.

## Daftar file

```
app/Console/Commands/ExpirePendingOrders.php   perintah pelepas stok
app/Exceptions/CheckoutException.php
app/Http/Controllers/                          Auth, Event, Checkout, Order, Ticket, Scanner, Webhook, Admin/*
app/Http/Middleware/EnsureRole.php             pembatas akses admin / petugas
app/Models/                                    User, Event, TicketType, Order, OrderItem, Payment, Ticket, ScanLog
app/Providers/AppServiceProvider.php           (menimpa) daftar webhook + @rupiah
app/Services/CheckoutService.php               pesanan + penahanan stok atomik
app/Services/PaymentService.php                Midtrans, status pembayaran, penerbitan tiket
app/Support/TicketCode.php                     pembuat/pemeriksa tanda tangan QR
app/Support/QrRenderer.php                     gambar QR (SVG)
config/ticketing.php                           pengaturan sistem tiket
database/migrations/                           4 file migration
database/seeders/DatabaseSeeder.php            (menimpa) data contoh
resources/views/                               semua tampilan (Blade + Tailwind)
routes/web.php, routes/console.php             (menimpa)
tests/Feature/                                 tes otomatis (ExampleTest.php menimpa bawaan)
env-tambahan.txt                                 baris tambahan untuk .env
```
