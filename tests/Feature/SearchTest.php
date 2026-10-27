<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_search_by_name_and_sku(): void
    {
        $user = User::factory()->staff()->create();
        Product::factory()->create(['name' => 'Kopi Arabika Gayo', 'sku' => 'KOP-001']);
        Product::factory()->create(['name' => 'Dripper V60 Keramik', 'sku' => 'SDH-001']);

        $this->actingAs($user)->get('/products?q=Arabika')
            ->assertSee('Kopi Arabika Gayo')->assertDontSee('Dripper V60 Keramik');

        $this->actingAs($user)->get('/products?q=SDH-001')
            ->assertSee('Dripper V60 Keramik')->assertDontSee('Kopi Arabika Gayo');
    }

    public function test_product_filter_by_category(): void
    {
        $user = User::factory()->staff()->create();
        $a = Category::factory()->create();
        $b = Category::factory()->create();
        Product::factory()->create(['name' => 'Barang Kategori A', 'category_id' => $a->id]);
        Product::factory()->create(['name' => 'Barang Kategori B', 'category_id' => $b->id]);

        $this->actingAs($user)->get('/products?category=' . $a->id)
            ->assertSee('Barang Kategori A')->assertDontSee('Barang Kategori B');
    }

    public function test_product_pagination_keeps_filter(): void
    {
        $user = User::factory()->staff()->create();
        $category = Category::factory()->create();
        Product::factory()->count(12)->create(['category_id' => $category->id]);

        $response = $this->actingAs($user)->get('/products?category=' . $category->id);

        $response->assertOk();
        $response->assertSee('page=2', false);
        $response->assertSee('category=' . $category->id, false);
        $this->assertCount(10, $response->viewData('products'));
    }

    public function test_sales_filter_by_status_and_date(): void
    {
        $owner = User::factory()->owner()->create();

        Sale::create([
            'user_id' => $owner->id, 'sale_number' => 'SL-OLD-1', 'total' => 1000,
            'status' => 'completed', 'sold_at' => '2026-01-10 10:00:00',
        ]);
        Sale::create([
            'user_id' => $owner->id, 'sale_number' => 'SL-NEW-1', 'total' => 2000,
            'status' => 'completed', 'sold_at' => '2026-03-10 10:00:00',
        ]);
        Sale::create([
            'user_id' => $owner->id, 'sale_number' => 'SL-CAN-1', 'total' => 3000,
            'status' => 'cancelled', 'sold_at' => '2026-03-11 10:00:00',
        ]);

        $this->actingAs($owner)->get('/sales?date_from=2026-03-01&date_to=2026-03-31')
            ->assertSee('SL-NEW-1')->assertSee('SL-CAN-1')->assertDontSee('SL-OLD-1');

        $this->actingAs($owner)->get('/sales?status=cancelled')
            ->assertSee('SL-CAN-1')->assertDontSee('SL-NEW-1');
    }

    public function test_dashboard_numbers_come_from_database(): void
    {
        $owner = User::factory()->owner()->create();
        Product::factory()->count(3)->create(['stock' => 1, 'minimum_stock' => 5]);

        $response = $this->actingAs($owner)->get('/dashboard');

        $response->assertOk();
        $this->assertSame(3, $response->viewData('stats')['total_products']);
        $this->assertSame(3, $response->viewData('stats')['low_stock_count']);
    }
}
