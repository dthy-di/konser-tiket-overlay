<?php

namespace App\Providers;

use App\Http\Controllers\WebhookController;
use App\Support\TicketCode;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            TicketCode::class,
            fn () => new TicketCode((string) config('ticketing.qr_secret'))
        );
    }

    public function boot(): void
    {
        // Webhook dari payment gateway: tanpa session dan tanpa CSRF (grup middleware "api").
        // Keamanannya ada di verifikasi signature di WebhookController.
        Route::middleware('api')->prefix('api')->group(function () {
            Route::post('webhooks/midtrans', [WebhookController::class, 'midtrans'])
                ->name('webhooks.midtrans');
        });

        // Pemakaian di Blade: @rupiah($harga)  ->  Rp 150.000
        Blade::directive('rupiah', fn ($expression) => "<?php echo 'Rp '.number_format((float) ({$expression}), 0, ',', '.'); ?>");
    }
}
