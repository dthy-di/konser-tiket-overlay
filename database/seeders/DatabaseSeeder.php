<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

/** Data contoh untuk development. Tidak berjalan di production. */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('Seeder demo tidak dijalankan di production. Buat akun admin manual lewat: php artisan tinker');

            return;
        }

        $accounts = [
            ['Admin Panitia', 'admin@konser.test', User::ROLE_ADMIN],
            ['Petugas Gate', 'petugas@konser.test', User::ROLE_SCANNER],
            ['Pembeli Demo', 'pembeli@konser.test', User::ROLE_CUSTOMER],
        ];

        foreach ($accounts as [$name, $email, $role]) {
            User::firstOrNew(['email' => $email])
                ->forceFill([
                    'name' => $name,
                    'phone' => '081234567890',
                    'role' => $role,
                    'password' => 'password',
                    'email_verified_at' => now(),
                ])
                ->save();
        }

        $event = Event::firstOrCreate(
            ['slug' => 'nusantara-fest-2026'],
            [
                'name' => 'Nusantara Fest 2026',
                'description' => "Festival musik terbesar tahun ini.\nBanyak penampil, satu malam yang tak terlupakan.",
                'venue' => 'Stadion Utama',
                'city' => 'Medan',
                'starts_at' => now()->addMonths(2)->setTime(19, 0),
                'status' => Event::PUBLISHED,
            ]
        );

        if ($event->wasRecentlyCreated) {
            // Total kuota 10.000 tiket
            $event->ticketTypes()->createMany([
                ['name' => 'Festival', 'description' => 'Berdiri, area umum', 'price' => 350000, 'quota' => 7000, 'sort_order' => 0],
                ['name' => 'VIP', 'description' => 'Area depan panggung', 'price' => 1000000, 'quota' => 2500, 'sort_order' => 1],
                ['name' => 'VVIP', 'description' => 'Area terdepan + merchandise', 'price' => 2500000, 'quota' => 500, 'sort_order' => 2],
            ]);
        }
    }
}
