<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    // ---------- Pendaftaran & login ----------

    public function test_visitor_can_register_and_becomes_customer(): void
    {
        $this->post('/register', [
            'name' => 'Pembeli Baru',
            'email' => 'pembeli@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('/shop');

        $this->assertDatabaseHas('users', ['email' => 'pembeli@example.com', 'role' => 'customer']);
        $this->assertAuthenticated();
    }

    public function test_register_ignores_role_sent_by_browser(): void
    {
        $this->post('/register', [
            'name' => 'Nakal', 'email' => 'nakal@example.com',
            'password' => 'password123', 'password_confirmation' => 'password123',
            'role' => 'owner', // harus diabaikan
        ]);

        $this->assertDatabaseHas('users', ['email' => 'nakal@example.com', 'role' => 'customer']);
    }

    public function test_register_validates_input(): void
    {
        User::factory()->create(['email' => 'ada@example.com']);

        $this->post('/register', [
            'name' => '', 'email' => 'ada@example.com',
            'password' => 'pendek', 'password_confirmation' => 'beda',
        ])->assertSessionHasErrors(['name', 'email', 'password']);

        $this->assertGuest();
    }

    public function test_customer_login_goes_to_shop(): void
    {
        User::factory()->customer()->create(['email' => 'c@example.com', 'password' => 'password123']);

        $this->post('/login', ['email' => 'c@example.com', 'password' => 'password123'])
            ->assertRedirect('/shop');
    }

    // ---------- Pembatasan akses ----------

    public function test_customer_cannot_open_backoffice_pages(): void
    {
        $customer = User::factory()->customer()->create();
        $product = Product::factory()->create();

        foreach (['/products', "/products/{$product->id}", '/low-stock', '/purchases', '/sales', '/sales/create',
            '/categories', '/suppliers', '/users', '/products/create'] as $url) {
            $this->actingAs($customer)->get($url)->assertForbidden();
        }

        $this->actingAs($customer)->post('/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ])->assertForbidden();
    }

    public function test_dashboard_redirects_customer_to_shop(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)->get('/dashboard')->assertRedirect('/shop');
    }

    public function test_staff_and_owner_cannot_open_shop_pages(): void
    {
        $this->actingAs(User::factory()->staff()->create())->get('/shop')->assertForbidden();
        $this->actingAs(User::factory()->owner()->create())->get('/cart')->assertForbidden();
    }

    public function test_guest_is_redirected_from_shop(): void
    {
        $this->get('/shop')->assertRedirect('/login');
    }

    // ---------- Katalog & keranjang ----------

    public function test_customer_can_browse_and_search_catalog(): void
    {
        $customer = User::factory()->customer()->create();
        Product::factory()->create(['name' => 'Kopi Arabika Contoh']);
        Product::factory()->create(['name' => 'Dripper Contoh']);

        $this->actingAs($customer)->get('/shop')->assertOk()->assertSee('Dripper Contoh');

        $this->actingAs($customer)->get('/shop?q=Arabika')
            ->assertOk()
            ->assertSee('Kopi Arabika Contoh')
            ->assertDontSee('Dripper Contoh');
    }

    public function test_add_to_cart_merges_quantity_and_respects_stock(): void
    {
        $customer = User::factory()->customer()->create();
        $product = Product::factory()->create(['stock' => 5]);

        // sudah ada 2 di keranjang, tambah 2 lagi: digabung jadi 4
        $this->actingAs($customer)->withSession(['cart' => [$product->id => 2]])
            ->post('/cart', ['product_id' => $product->id, 'quantity' => 2])
            ->assertSessionHas('cart', [$product->id => 4]);

        // sudah ada 4, tambah 2 lagi = 6 > stok 5: ditolak, keranjang tetap 4
        $this->actingAs($customer)->withSession(['cart' => [$product->id => 4]])
            ->post('/cart', ['product_id' => $product->id, 'quantity' => 2])
            ->assertSessionHas('error')
            ->assertSessionHas('cart', [$product->id => 4]);
    }

    public function test_cart_update_and_remove(): void
    {
        $customer = User::factory()->customer()->create();
        $product = Product::factory()->create(['stock' => 10]);

        $this->actingAs($customer)->withSession(['cart' => [$product->id => 1]])
            ->patch("/cart/{$product->id}", ['quantity' => 3])
            ->assertSessionHas('cart', [$product->id => 3]);

        $this->actingAs($customer)->withSession(['cart' => [$product->id => 3]])
            ->delete("/cart/{$product->id}")
            ->assertSessionHas('cart', []);
    }

    // ---------- Checkout ----------

    public function test_checkout_creates_order_reduces_stock_and_uses_server_price(): void
    {
        $customer = User::factory()->customer()->create();
        $product = Product::factory()->create(['stock' => 10, 'selling_price' => 20000]);

        // browser mencoba mengirim harga palsu saat checkout: harus diabaikan
        $this->actingAs($customer)->withSession(['cart' => [$product->id => 3]])
            ->post('/cart/checkout', ['total' => 1, 'unit_price' => 1])
            ->assertSessionHasNoErrors()
            ->assertSessionMissing('cart');

        $sale = Sale::first();
        $this->assertSame($customer->id, $sale->user_id);
        $this->assertSame('60000.00', $sale->total);
        $this->assertSame(7, $product->fresh()->stock);
    }

    public function test_checkout_with_empty_cart_is_rejected(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)->post('/cart/checkout')->assertSessionHas('error');

        $this->assertSame(0, Sale::count());
    }

    public function test_checkout_rejected_when_stock_dropped_after_adding_to_cart(): void
    {
        $customer = User::factory()->customer()->create();
        $product = Product::factory()->create(['stock' => 5]);

        // stok berkurang karena pembeli lain sudah membeli (keranjang masih minta 5)
        $product->update(['stock' => 2]);

        $this->actingAs($customer)->withSession(['cart' => [$product->id => 5]])
            ->post('/cart/checkout')
            ->assertSessionHasErrors('items')
            // keranjang tidak dikosongkan kalau checkout gagal
            ->assertSessionHas('cart', [$product->id => 5]);

        $this->assertSame(2, $product->fresh()->stock);
        $this->assertSame(0, Sale::count());
    }

    // ---------- Pesanan saya ----------

    public function test_customer_sees_only_own_orders(): void
    {
        $mine = User::factory()->customer()->create();
        $other = User::factory()->customer()->create();
        $product = Product::factory()->create(['stock' => 10]);
        $service = app(SaleService::class);

        $myOrder = $service->create($mine, [['product_id' => $product->id, 'quantity' => 1]]);
        $otherOrder = $service->create($other, [['product_id' => $product->id, 'quantity' => 1]]);

        $this->actingAs($mine)->get('/orders')
            ->assertOk()
            ->assertSee($myOrder->sale_number)
            ->assertDontSee($otherOrder->sale_number);

        $this->actingAs($mine)->get("/orders/{$myOrder->id}")->assertOk();
        $this->actingAs($mine)->get("/orders/{$otherOrder->id}")->assertForbidden();
    }

    // ---------- Pengajuan pembatalan ----------

    private function makeOrder(User $customer, int $qty = 2): array
    {
        $product = Product::factory()->create(['stock' => 10]);
        $sale = app(SaleService::class)->create($customer, [['product_id' => $product->id, 'quantity' => $qty]]);

        return [$product, $sale];
    }

    public function test_customer_can_request_cancel_but_stock_is_unchanged(): void
    {
        $customer = User::factory()->customer()->create();
        [$product, $sale] = $this->makeOrder($customer);

        $this->actingAs($customer)->post("/orders/{$sale->id}/cancel-request", ['reason' => 'Salah pilih produk'])
            ->assertSessionHasNoErrors();

        $sale->refresh();
        $this->assertSame('requested', $sale->cancel_status);
        $this->assertSame('completed', $sale->status);
        $this->assertSame(8, $product->fresh()->stock); // belum kembali
    }

    public function test_cancel_request_requires_reason(): void
    {
        $customer = User::factory()->customer()->create();
        [, $sale] = $this->makeOrder($customer);

        $this->actingAs($customer)->post("/orders/{$sale->id}/cancel-request", ['reason' => ''])
            ->assertSessionHasErrors('reason');
        $this->actingAs($customer)->post("/orders/{$sale->id}/cancel-request", ['reason' => 'abc'])
            ->assertSessionHasErrors('reason');

        $this->assertNull($sale->fresh()->cancel_status);
    }

    public function test_customer_cannot_request_cancel_for_other_customers_order(): void
    {
        $owner = User::factory()->customer()->create();
        $intruder = User::factory()->customer()->create();
        [, $sale] = $this->makeOrder($owner);

        $this->actingAs($intruder)->post("/orders/{$sale->id}/cancel-request", ['reason' => 'Iseng saja'])
            ->assertForbidden();

        $this->assertNull($sale->fresh()->cancel_status);
    }

    public function test_cannot_request_cancel_twice(): void
    {
        $customer = User::factory()->customer()->create();
        [, $sale] = $this->makeOrder($customer);

        $this->actingAs($customer)->post("/orders/{$sale->id}/cancel-request", ['reason' => 'Alasan pertama']);
        $this->actingAs($customer)->post("/orders/{$sale->id}/cancel-request", ['reason' => 'Alasan kedua'])
            ->assertSessionHasErrors('status');

        $this->assertSame('Alasan pertama', $sale->fresh()->cancel_reason);
    }

    public function test_customer_cannot_cancel_directly_or_decide_requests(): void
    {
        $customer = User::factory()->customer()->create();
        [$product, $sale] = $this->makeOrder($customer);

        $this->actingAs($customer)->post("/sales/{$sale->id}/cancel")->assertForbidden();
        $this->actingAs($customer)->post("/sales/{$sale->id}/reject-cancel")->assertForbidden();

        $this->assertSame('completed', $sale->fresh()->status);
        $this->assertSame(8, $product->fresh()->stock);
    }

    public function test_staff_cannot_decide_cancel_requests(): void
    {
        $customer = User::factory()->customer()->create();
        [, $sale] = $this->makeOrder($customer);
        $this->actingAs($customer)->post("/orders/{$sale->id}/cancel-request", ['reason' => 'Ingin batal']);

        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)->post("/sales/{$sale->id}/cancel")->assertForbidden();
        $this->actingAs($staff)->post("/sales/{$sale->id}/reject-cancel")->assertForbidden();

        $this->assertSame('requested', $sale->fresh()->cancel_status);
    }

    public function test_owner_approves_request_and_stock_is_restored(): void
    {
        $customer = User::factory()->customer()->create();
        $owner = User::factory()->owner()->create();
        [$product, $sale] = $this->makeOrder($customer);
        $this->actingAs($customer)->post("/orders/{$sale->id}/cancel-request", ['reason' => 'Ingin batal']);

        $this->actingAs($owner)->post("/sales/{$sale->id}/cancel")->assertSessionHasNoErrors();

        $sale->refresh();
        $this->assertSame('cancelled', $sale->status);
        $this->assertSame('approved', $sale->cancel_status);
        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_owner_rejects_request_and_order_stays_valid(): void
    {
        $customer = User::factory()->customer()->create();
        $owner = User::factory()->owner()->create();
        [$product, $sale] = $this->makeOrder($customer);
        $this->actingAs($customer)->post("/orders/{$sale->id}/cancel-request", ['reason' => 'Ingin batal']);

        $this->actingAs($owner)->post("/sales/{$sale->id}/reject-cancel", ['response' => 'Barang sudah dikirim'])
            ->assertSessionHasNoErrors();

        $sale->refresh();
        $this->assertSame('completed', $sale->status);
        $this->assertSame('rejected', $sale->cancel_status);
        $this->assertSame('Barang sudah dikirim', $sale->cancel_response);
        $this->assertSame(8, $product->fresh()->stock);
    }

    public function test_reject_without_pending_request_is_refused(): void
    {
        $customer = User::factory()->customer()->create();
        $owner = User::factory()->owner()->create();
        [, $sale] = $this->makeOrder($customer);

        $this->actingAs($owner)->post("/sales/{$sale->id}/reject-cancel")->assertSessionHasErrors('status');
    }

    public function test_owner_dashboard_shows_pending_requests(): void
    {
        $customer = User::factory()->customer()->create();
        $owner = User::factory()->owner()->create();
        [, $sale] = $this->makeOrder($customer);
        $this->actingAs($customer)->post("/orders/{$sale->id}/cancel-request", ['reason' => 'Ingin batal']);

        $this->actingAs($owner)->get('/dashboard')->assertOk()->assertSee('permintaan pembatalan');
        $this->actingAs($owner)->get('/sales?cancel=requested')->assertOk()->assertSee($sale->sale_number);
    }

    public function test_customer_with_orders_cannot_be_deleted_by_owner(): void
    {
        $customer = User::factory()->customer()->create();
        $owner = User::factory()->owner()->create();
        $this->makeOrder($customer);

        $this->actingAs($owner)->delete("/users/{$customer->id}");

        $this->assertDatabaseHas('users', ['id' => $customer->id]);
    }
}
