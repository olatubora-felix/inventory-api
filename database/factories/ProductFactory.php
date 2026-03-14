<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\UnitOfMeasure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'sku' => strtoupper(fake()->unique()->bothify('???-###-???')),
            'description' => fake()->optional()->sentence(),
            'category_id' => Category::factory(),
            'unit_of_measure_id' => UnitOfMeasure::factory(),
            'reorder_level' => fake()->randomFloat(3, 0, 50),
            'cost_price' => fake()->optional()->randomFloat(2, 100, 5000),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
