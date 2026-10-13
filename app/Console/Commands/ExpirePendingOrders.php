<?php

namespace App\Console\Commands;

use App\Services\PaymentService;
use Illuminate\Console\Command;

class ExpirePendingOrders extends Command
{
    protected $signature = 'orders:expire';

    protected $description = 'Akhiri pesanan yang lewat batas waktu bayar dan kembalikan stok tiketnya';

    public function handle(PaymentService $payments): int
    {
        $count = $payments->expirePending();

        $this->info("{$count} pesanan kedaluwarsa, stok dikembalikan.");

        return self::SUCCESS;
    }
}
