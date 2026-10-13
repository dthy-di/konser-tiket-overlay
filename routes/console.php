<?php

use Illuminate\Support\Facades\Schedule;

// Melepas stok dari pesanan yang tidak dibayar. Wajib berjalan setiap menit:
//   development : php artisan schedule:work
//   produksi    : cron  * * * * * cd /path/ke/project && php artisan schedule:run >> /dev/null 2>&1
Schedule::command('orders:expire')->everyMinute()->withoutOverlapping();
