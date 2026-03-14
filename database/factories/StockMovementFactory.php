<?php

namespace Database\Factories;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockMovement>
 */
class StockMovementFactory extends Factory
{
    public function definition(): array
    {
        $quantityBefore = fake()->randomFloat(3, 0, 500);
        $quantity = fake()->randomFloat(3, 0.001, 100);
        $type = fake()->randomElement(StockMovementType::cases());
        $quantityAfter = $type->increasesStock()
            ? $quantityBefore + $quantity
            : max(0, $quantityBefore - $quantity);

        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'type' => $type,
            'quantity' => $quantity,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $quantityAfter,
            'unit_cost' => fake()->optional()->randomFloat(2, 10, 1000),
            'reference_number' => fake()->optional()->numerify('REF-#####'),
            'notes' => fake()->optional()->sentence(),
            'occurred_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
