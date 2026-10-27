<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private function validData(array $override = []): array
    {
        return array_merge([
            'category_id' => Category::factory()->create()->id,
            'sku' => 'TES-001',
            'name' => 'Produk Tes',
            'purchase_price' => 10000,
            'selling_price' => 15000,
            'stock' => 5,
            'minimum_stock' => 2,
        ], $override);
    }

    public function test_owner_can_create_product(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)->post('/products', $this->validData())->assertSessionHasNoErrors();

        $this->assertDatabaseHas('products', ['sku' => 'TES-001', 'stock' => 5]);
    }

    public function test_duplicate_sku_is_rejected(): void
    {
        $owner = User::factory()->owner()->create();
        Product::factory()->create(['sku' => 'TES-001']);

        $this->actingAs($owner)->post('/products', $this->validData())->assertSessionHasErrors('sku');

        $this->assertSame(1, Product::count());
    }

    public function test_negative_price_is_rejected(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->post('/products', $this->validData(['selling_price' => -1]))
            ->assertSessionHasErrors('selling_price');

        $this->assertSame(0, Product::count());
    }

    public function test_invalid_category_is_rejected(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->post('/products', $this->validData(['category_id' => 9999]))
            ->assertSessionHasErrors('category_id');
    }
}
