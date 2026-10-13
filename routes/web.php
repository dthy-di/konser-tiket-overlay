
<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ScannerController;
use App\Http\Controllers\TicketController;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

// --------------------------------------------------
// HALAMAN PUBLIK
// --------------------------------------------------
Route::get('/', [EventController::class, 'index'])->name('home');

Route::get('/events/{event}', [EventController::class, 'show'])
    ->name('events.show');

// --------------------------------------------------
// LOGIN DAN REGISTRASI
// --------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1');

    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:10,1');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// --------------------------------------------------
// PEMBELI
// --------------------------------------------------
Route::middleware('auth')->group(function () {
    Route::post('/events/{event}/checkout', [CheckoutController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('checkout.store');

    Route::get('/orders/{order}', [OrderController::class, 'show'])
        ->name('orders.show');

    Route::get('/orders/{order}/status', [OrderController::class, 'status'])
        ->name('orders.status');

    Route::post('/orders/{order}/fake-pay', [OrderController::class, 'fakePay'])
        ->name('orders.fake-pay');

    Route::get('/my-tickets', [TicketController::class, 'index'])
        ->name('tickets.index');

    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])
        ->name('tickets.show');
});

// --------------------------------------------------
// SCANNER / PETUGAS PINTU MASUK
// --------------------------------------------------
Route::middleware(['auth', EnsureRole::class . ':scanner,admin'])->group(function () {
    Route::get('/scanner', [ScannerController::class, 'index'])
        ->name('scanner.index');

    Route::post('/scanner/check', [ScannerController::class, 'check'])
        ->middleware('throttle:240,1')
        ->name('scanner.check');
});

// --------------------------------------------------
// ADMIN
// --------------------------------------------------
Route::middleware(['auth', EnsureRole::class . ':admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/events/create', [AdminEventController::class, 'create'])
            ->name('events.create');

        Route::post('/events', [AdminEventController::class, 'store'])
            ->name('events.store');

        Route::post('/events/{event}/toggle', [AdminEventController::class, 'toggle'])
            ->name('events.toggle');

        Route::delete('/events/{event}', [AdminEventController::class, 'destroy'])
            ->name('events.destroy');
    });

// --------------------------------------------------
// WEBHOOK MIDTRANS
// POST /api/webhooks/midtrans didaftarkan di AppServiceProvider
