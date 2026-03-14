<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockLevel>
 */
class StockLevelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'quantity_on_hand' => fake()->randomFloat(3, 0, 500),
            'last_updated_at' => now(),
        ];
    }
}
