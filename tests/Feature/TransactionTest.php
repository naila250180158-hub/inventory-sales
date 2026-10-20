<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_increases_stock(): void
    {
        $staff = User::factory()->staff()->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);

        $this->actingAs($staff)->post('/purchases', [
            'supplier_id' => $supplier->id,
            'items' => [['product_id' => $product->id, 'quantity' => 7, 'unit_cost' => 5000]],
        ])->assertSessionHasNoErrors();

        $this->assertSame(17, $product->fresh()->stock);
        $this->assertSame(1, Purchase::count());
        $this->assertSame('35000.00', Purchase::first()->total);
    }

    public function test_sale_decreases_stock_and_total_is_computed_by_server(): void
    {
        $staff = User::factory()->staff()->create();
        $product = Product::factory()->create(['stock' => 10, 'selling_price' => 20000]);

        // browser mencoba mengirim harga palsu: harus diabaikan
        $this->actingAs($staff)->post('/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 3, 'unit_price' => 1]],
            'total' => 1,
        ])->assertSessionHasNoErrors();

        $this->assertSame(7, $product->fresh()->stock);
        $this->assertSame('60000.00', Sale::first()->total);
    }

    public function test_sale_with_insufficient_stock_is_rejected_without_changes(): void
    {
        $staff = User::factory()->staff()->create();
        $product = Product::factory()->create(['stock' => 4]);

        $this->actingAs($staff)->post('/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 6]],
        ])->assertSessionHasErrors('items');

        $this->assertSame(4, $product->fresh()->stock);
        $this->assertSame(0, Sale::count());
    }

    public function test_zero_or_negative_quantity_is_rejected(): void
    {
        $staff = User::factory()->staff()->create();
        $product = Product::factory()->create(['stock' => 4]);

        $this->actingAs($staff)->post('/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 0]],
        ])->assertSessionHasErrors('items.0.quantity');

        $this->actingAs($staff)->post('/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => -2]],
        ])->assertSessionHasErrors('items.0.quantity');

        $this->assertSame(4, $product->fresh()->stock);
    }

    public function test_sale_is_atomic_when_second_item_fails(): void
    {
        $staff = User::factory()->staff()->create();
        $ok = Product::factory()->create(['stock' => 10]);
        $low = Product::factory()->create(['stock' => 1]);

        // item pertama valid, item kedua stok tidak cukup -> SEMUA harus batal
        $this->actingAs($staff)->post('/sales', [
            'items' => [
                ['product_id' => $ok->id, 'quantity' => 3],
                ['product_id' => $low->id, 'quantity' => 5],
            ],
        ])->assertSessionHasErrors('items');

        $this->assertSame(10, $ok->fresh()->stock);
        $this->assertSame(1, $low->fresh()->stock);
        $this->assertSame(0, Sale::count());
        $this->assertDatabaseCount('sale_items', 0);
    }

    public function test_duplicate_lines_of_same_product_are_combined_for_stock_check(): void
    {
        $staff = User::factory()->staff()->create();
        $product = Product::factory()->create(['stock' => 5]);

        // 3 + 3 = 6 > 5, tidak boleh lolos hanya karena dipecah dua baris
        $this->actingAs($staff)->post('/sales', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3],
                ['product_id' => $product->id, 'quantity' => 3],
            ],
        ])->assertSessionHasErrors('items');

        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_admin_cancels_sale_and_stock_is_restored(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['stock' => 10]);

        $this->actingAs($admin)->post('/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 4]],
        ]);
        $sale = Sale::first();
        $this->assertSame(6, $product->fresh()->stock);

        $this->actingAs($admin)->post("/sales/{$sale->id}/cancel")->assertSessionHasNoErrors();

        $this->assertSame(10, $product->fresh()->stock);
        $this->assertSame('cancelled', $sale->fresh()->status);

        // dibatalkan dua kali tidak boleh menggandakan stok
        $this->actingAs($admin)->post("/sales/{$sale->id}/cancel")->assertSessionHasErrors('status');
        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_product_used_in_transaction_cannot_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['stock' => 10]);

        $this->actingAs($admin)->post('/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ]);

        $this->actingAs($admin)->delete("/products/{$product->id}");

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }
}
