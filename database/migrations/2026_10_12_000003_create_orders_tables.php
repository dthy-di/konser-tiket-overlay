<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('code', 40)->unique();             // INV-261012-XXXXXXXX (juga dipakai sebagai order_id Midtrans)
            $table->string('status', 20)->default('pending'); // pending | paid | expired | cancelled | refund_needed | refunded
            $table->unsignedBigInteger('total');
            $table->dateTime('expires_at')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'expires_at']);          // untuk job yang mengakhiri pesanan kedaluwarsa
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_type_id')->constrained();
            $table->unsignedSmallInteger('quantity');
            $table->unsignedBigInteger('unit_price');         // harga saat dibeli (aman kalau harga berubah nanti)
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('gateway', 20);                    // midtrans | fake
            $table->string('method', 50)->nullable();         // bank_transfer, qris, gopay, credit_card, ...
            $table->string('gateway_ref', 100)->nullable();   // transaction_id dari gateway
            $table->string('status', 20)->default('pending');
            $table->string('snap_token', 100)->nullable();
            $table->json('payload')->nullable();              // balasan/notifikasi terakhir dari gateway
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
