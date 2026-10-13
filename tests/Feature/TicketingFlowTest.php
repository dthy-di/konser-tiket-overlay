<?php

namespace Tests\Feature;

use App\Exceptions\CheckoutException;
use App\Models\Event;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use App\Services\CheckoutService;
use App\Services\PaymentService;
use App\Support\TicketCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Tes untuk bagian yang paling kritis: stok, pembayaran, webhook, dan scan. Jalankan: php artisan test */
class TicketingFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'ticketing.payment_driver' => 'fake',
            'ticketing.max_per_order' => 4,
            'ticketing.max_pending_orders' => 10,
        ]);
    }

    private function makeEvent(int $quota): Event
    {
        $event = Event::create([
            'name' => 'Tes Fest',
            'slug' => 'tes-fest',
            'venue' => 'Stadion',
            'starts_at' => now()->addWeek(),
            'status' => Event::PUBLISHED,
        ]);

        $event->ticketTypes()->create(['name' => 'Festival', 'price' => 100000, 'quota' => $quota]);

        return $event;
    }

    private function makeUser(string $email = 'pembeli@example.test', string $role = 'customer'): User
    {
        return User::forceCreate([
            'name' => 'Tester',
            'email' => $email,
            'password' => 'password',
            'role' => $role,
        ]);
    }

    private function order(Event $event, int $qty, ?User $user = null): Order
    {
        $typeId = $event->ticketTypes()->first()->id;

        return app(CheckoutService::class)->createOrder($user ?? $this->makeUser(), $event, [$typeId => $qty]);
    }

    public function test_stok_tidak_pernah_terjual_melebihi_kuota(): void
    {
        $event = $this->makeEvent(2);
        $typeId = $event->ticketTypes()->first()->id;

        $this->order($event, 2);

        try {
            app(CheckoutService::class)->createOrder($this->makeUser('lain@example.test'), $event, [$typeId => 1]);
            $this->fail('Pesanan seharusnya ditolak karena stok habis.');
        } catch (CheckoutException) {
            // benar: ditolak
        }

        $this->assertEquals(2, TicketType::find($typeId)->sold);
    }

    public function test_pembayaran_dua_kali_hanya_menerbitkan_tiket_sekali(): void
    {
        $order = $this->order($this->makeEvent(5), 2);
        $payments = app(PaymentService::class);

        $payments->markPaid($order);
        $payments->markPaid($order);

        $this->assertSame(Order::PAID, $order->fresh()->status);
        $this->assertEquals(2, Ticket::count());
    }

    public function test_pesanan_kedaluwarsa_mengembalikan_stok(): void
    {
        $event = $this->makeEvent(5);
        $order = $this->order($event, 2);
        $this->assertEquals(2, $event->ticketTypes()->first()->sold);

        $order->update(['expires_at' => now()->subMinute()]);

        $this->assertEquals(1, app(PaymentService::class)->expirePending());
        $this->assertSame(Order::EXPIRED, $order->fresh()->status);
        $this->assertEquals(0, $event->ticketTypes()->first()->sold);
    }

    public function test_webhook_midtrans_wajib_signature_valid_dan_idempotent(): void
    {
        config(['ticketing.payment_driver' => 'midtrans', 'ticketing.midtrans.server_key' => 'SB-Mid-server-test']);

        $order = $this->order($this->makeEvent(5), 2);

        $body = [
            'order_id' => $order->code,
            'status_code' => '200',
            'gross_amount' => number_format($order->total, 2, '.', ''),
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
            'transaction_id' => 'trx-123',
        ];

        $this->postJson('/api/webhooks/midtrans', $body + ['signature_key' => 'palsu'])->assertStatus(403);
        $this->assertSame(Order::PENDING, $order->fresh()->status);

        $body['signature_key'] = hash('sha512', $body['order_id'].$body['status_code'].$body['gross_amount'].'SB-Mid-server-test');

        $this->postJson('/api/webhooks/midtrans', $body)->assertOk();
        $this->postJson('/api/webhooks/midtrans', $body)->assertOk(); // dikirim ulang oleh gateway

        $this->assertSame(Order::PAID, $order->fresh()->status);
        $this->assertEquals(2, Ticket::count());
    }

    public function test_tiket_hanya_bisa_di_scan_sekali_dan_qr_palsu_ditolak(): void
    {
        $event = $this->makeEvent(5);
        $order = $this->order($event, 1);
        app(PaymentService::class)->markPaid($order);

        $ticket = Ticket::firstOrFail();
        $payload = app(TicketCode::class)->payload($ticket->code);
        $staff = $this->makeUser('petugas@example.test', User::ROLE_SCANNER);
        $request = ['payload' => $payload, 'event_id' => $event->id];

        $this->actingAs($staff)->postJson(route('scanner.check'), $request)
            ->assertOk()->assertJsonPath('result', 'valid');

        $this->actingAs($staff)->postJson(route('scanner.check'), $request)
            ->assertOk()->assertJsonPath('result', 'duplicate');

        $this->actingAs($staff)->postJson(route('scanner.check'), [
            'payload' => 'TKT-'.$ticket->code.'-000000000000',
            'event_id' => $event->id,
        ])->assertOk()->assertJsonPath('result', 'invalid');
    }
}
