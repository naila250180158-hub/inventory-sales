<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $purchase = fake()->numberBetween(5, 300) * 1000;

        return [
            'category_id' => Category::factory(),
            'sku' => fake()->unique()->bothify('SKU-####??'),
            'name' => fake()->unique()->words(3, true),
            'purchase_price' => $purchase,
            'selling_price' => (int) round($purchase * 1.25),
            'stock' => fake()->numberBetween(10, 50),
            'minimum_stock' => 5,
        ];
    }
}
