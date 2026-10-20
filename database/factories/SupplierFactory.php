<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'name' => 'PT ' . fake()->unique()->company(),
            'phone' => '08' . fake()->numerify('##########'),
            'email' => fake()->unique()->safeEmail(),
        ];
    }
}
