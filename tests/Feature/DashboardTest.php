<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use App\Services\PurchaseService;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_dashboard_shows_chart_and_top_products(): void
    {
        $owner = User::factory()->owner()->create();
        $product = Product::factory()->create(['name' => 'Produk Terlaris Uji', 'stock' => 20]);
        app(SaleService::class)->create($owner, [['product_id' => $product->id, 'quantity' => 3]]);

        $this->actingAs($owner)->get('/dashboard')
            ->assertOk()
            ->assertSee('Tren 7 Hari Terakhir')
            ->assertSee('Produk Terlaris')
            ->assertSee('Produk Terlaris Uji');
    }

    public function test_chart_totals_follow_transactions_and_ignore_cancelled(): void
    {
        $owner = User::factory()->owner()->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create(['stock' => 20, 'selling_price' => 10000]);
        $sales = app(SaleService::class);

        $sales->create($owner, [['product_id' => $product->id, 'quantity' => 3]]);   // 30.000
        $cancelled = $sales->create($owner, [['product_id' => $product->id, 'quantity' => 2]]); // 20.000, nanti dibatalkan
        $sales->cancel($cancelled);

        app(PurchaseService::class)->create($owner, $supplier->id, [
            ['product_id' => $product->id, 'quantity' => 5, 'unit_cost' => 4000],      // 20.000
        ]);

        $this->actingAs($owner)->get('/dashboard')
            ->assertViewHas('trend', function (array $trend) {
                // indeks terakhir = hari ini
                return $trend['sales'][6] === 30000.0
                    && $trend['purchases'][6] === 20000.0
                    && count($trend['labels']) === 7;
            });
    }

    public function test_staff_chart_only_counts_own_transactions(): void
    {
        $staff = User::factory()->staff()->create();
        $other = User::factory()->staff()->create();
        $product = Product::factory()->create(['stock' => 20, 'selling_price' => 10000]);
        $sales = app(SaleService::class);

        $sales->create($staff, [['product_id' => $product->id, 'quantity' => 1]]);  // 10.000
        $sales->create($other, [['product_id' => $product->id, 'quantity' => 4]]);  // 40.000 (bukan miliknya)

        $this->actingAs($staff)->get('/dashboard')
            ->assertViewHas('trend', fn (array $trend) => $trend['sales'][6] === 10000.0);
    }

    public function test_top_products_are_ordered_by_quantity_sold(): void
    {
        $owner = User::factory()->owner()->create();
        $few = Product::factory()->create(['name' => 'Sedikit Terjual', 'stock' => 20]);
        $many = Product::factory()->create(['name' => 'Banyak Terjual', 'stock' => 20]);
        $sales = app(SaleService::class);

        $sales->create($owner, [['product_id' => $few->id, 'quantity' => 1]]);
        $sales->create($owner, [['product_id' => $many->id, 'quantity' => 5]]);

        $this->actingAs($owner)->get('/dashboard')
            ->assertViewHas('topProducts', fn ($rows) => $rows->first()->product_id === $many->id);
    }

    public function test_unknown_page_shows_friendly_404(): void
    {
        $this->get('/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertSee('Halaman tidak ditemukan');
    }

    public function test_staff_dashboard_shows_only_own_recent_sales(): void
    {
        $staff = User::factory()->staff()->create();
        $other = User::factory()->staff()->create();
        $product = Product::factory()->create(['stock' => 20]);
        $sales = app(SaleService::class);

        $mine = $sales->create($staff, [['product_id' => $product->id, 'quantity' => 1]]);
        $theirs = $sales->create($other, [['product_id' => $product->id, 'quantity' => 1]]);

        $this->actingAs($staff)->get('/dashboard')
            ->assertOk()
            ->assertSee('Transaksi Terakhir Saya')
            ->assertSee($mine->sale_number)
            ->assertDontSee($theirs->sale_number);
    }

    public function test_only_owner_sees_cancel_notice_on_dashboard(): void
    {
        $customer = User::factory()->customer()->create();
        $product = Product::factory()->create(['stock' => 10]);
        $sale = app(SaleService::class)->create($customer, [['product_id' => $product->id, 'quantity' => 1]]);
        app(SaleService::class)->requestCancel($sale, 'Ingin batal');

        $this->actingAs(User::factory()->owner()->create())->get('/dashboard')
            ->assertSee('permintaan pembatalan menunggu keputusanmu');
        $this->actingAs(User::factory()->staff()->create())->get('/dashboard')
            ->assertDontSee('permintaan pembatalan menunggu keputusanmu');
    }
}
