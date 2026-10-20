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
            'name' => 'X', 'email' => 'x@example.com', 'password' => 'password123', 'role' => 'admin',
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

    public function test_staff_cannot_view_other_staff_sale_but_admin_can(): void
    {
        $staffA = User::factory()->staff()->create();
        $staffB = User::factory()->staff()->create();
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['stock' => 10]);

        $this->actingAs($staffA)->post('/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ]);
        $sale = Sale::first();

        $this->actingAs($staffB)->get("/sales/{$sale->id}")->assertForbidden();
        $this->actingAs($staffA)->get("/sales/{$sale->id}")->assertOk();
        $this->actingAs($admin)->get("/sales/{$sale->id}")->assertOk();
    }

    public function test_admin_can_manage_users(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/users')->assertOk();
        $this->actingAs($admin)->post('/users', [
            'name' => 'Staff Baru', 'email' => 'baru@example.com', 'password' => 'password123', 'role' => 'staff',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['email' => 'baru@example.com', 'role' => 'staff']);
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->delete("/users/{$admin->id}");

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
