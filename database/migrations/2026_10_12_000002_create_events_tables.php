<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 190)->unique();
            $table->text('description')->nullable();
            $table->string('venue', 150);
            $table->string('city', 80)->nullable();
            $table->string('poster_url', 500)->nullable();
            $table->dateTime('starts_at');
            $table->string('status', 20)->default('draft')->index(); // draft | published
            $table->timestamps();
        });

        Schema::create('ticket_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('name', 80);                       // Festival / VIP / VVIP ...
            $table->string('description', 255)->nullable();
            $table->unsignedBigInteger('price');              // rupiah, tanpa desimal
            $table->unsignedInteger('quota');
            $table->unsignedInteger('sold')->default(0);      // terjual + sedang ditahan (belum bayar)
            $table->dateTime('sale_start_at')->nullable();    // untuk early bird / tahap penjualan
            $table->dateTime('sale_end_at')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_types');
        Schema::dropIfExists('events');
    }
};
