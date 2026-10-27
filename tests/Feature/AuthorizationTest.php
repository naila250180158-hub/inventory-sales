<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_cannot_open_user_management(): void
    {
        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)->get('/users')->assertForbidden();
        $this->actingAs($staff)->post('/users', [
            'name' => 'X', 'email' => 'x@example.com', 'password' => 'password123', 'role' => 'owner',
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'x@example.com']);
    }

    public function test_staff_cannot_manage_master_data_even_with_direct_url(): void
    {
        $staff = User::factory()->staff()->create();
        $product = Product::factory()->create();

        $this->actingAs($staff)->get('/products/create')->assertForbidden();
        $this->actingAs($staff)->get('/categories')->assertForbidden();
        $this->actingAs($staff)->get('/suppliers')->assertForbidden();
        $this->actingAs($staff)->delete("/products/{$product->id}")->assertForbidden();
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_staff_can_view_products(): void
    {
        $staff = User::factory()->staff()->create();
        $product = Product::factory()->create();

        $this->actingAs($staff)->get('/products')->assertOk();
        $this->actingAs($staff)->get("/products/{$product->id}")->assertOk();
    }

    public function test_staff_cannot_cancel_transaction(): void
    {
        $staff = User::factory()->staff()->create();
        $product = Product::factory()->create(['stock' => 10]);

        $this->actingAs($staff)->post('/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
        ]);
        $sale = Sale::first();

        $this->actingAs($staff)->post("/sales/{$sale->id}/cancel")->assertForbidden();

        $this->assertSame('completed', $sale->fresh()->status);
        $this->assertSame(8, $product->fresh()->stock);
    }

    public function test_staff_cannot_view_other_staff_sale_but_owner_can(): void
    {
        $staffA = User::factory()->staff()->create();
        $staffB = User::factory()->staff()->create();
        $owner = User::factory()->owner()->create();
        $product = Product::factory()->create(['stock' => 10]);

        $this->actingAs($staffA)->post('/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ]);
        $sale = Sale::first();

        $this->actingAs($staffB)->get("/sales/{$sale->id}")->assertForbidden();
        $this->actingAs($staffA)->get("/sales/{$sale->id}")->assertOk();
        $this->actingAs($owner)->get("/sales/{$sale->id}")->assertOk();
    }

    public function test_owner_can_manage_users(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)->get('/users')->assertOk();
        $this->actingAs($owner)->post('/users', [
            'name' => 'Staff Baru', 'email' => 'baru@example.com', 'password' => 'password123', 'role' => 'staff',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['email' => 'baru@example.com', 'role' => 'staff']);
    }

    public function test_owner_cannot_delete_own_account(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)->delete("/users/{$owner->id}");

        $this->assertDatabaseHas('users', ['id' => $owner->id]);
    }

    public function test_owner_can_manage_master_data(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)->get('/categories')->assertOk();
        $this->actingAs($owner)->get('/suppliers')->assertOk();
        $this->actingAs($owner)->get('/products/create')->assertOk();
    }

    public function test_owner_can_cancel_transaction_and_stock_returns(): void
    {
        $owner = User::factory()->owner()->create();
        $staff = User::factory()->staff()->create();
        $product = Product::factory()->create(['stock' => 10]);

        $this->actingAs($staff)->post('/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 3]],
        ]);
        $sale = Sale::first();
        $this->assertSame(7, $product->fresh()->stock);

        $this->actingAs($owner)->post("/sales/{$sale->id}/cancel")->assertSessionHasNoErrors();

        $this->assertSame('cancelled', $sale->fresh()->status);
        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_owner_and_staff_get_their_own_dashboard(): void
    {
        $this->actingAs(User::factory()->owner()->create())->get('/dashboard')
            ->assertOk()->assertSee('Dashboard Owner');
        $this->actingAs(User::factory()->staff()->create())->get('/dashboard')
            ->assertOk()->assertSee('Dashboard Staff');
    }
}
