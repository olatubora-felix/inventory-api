<?php

namespace Database\Factories;

use App\Models\UnitOfMeasure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UnitOfMeasure>
 */
class UnitOfMeasureFactory extends Factory
{
    public function definition(): array
    {
        $units = [
            ['name' => 'Kilogram', 'abbreviation' => 'kg'],
            ['name' => 'Gram', 'abbreviation' => 'g'],
            ['name' => 'Liter', 'abbreviation' => 'L'],
            ['name' => 'Milliliter', 'abbreviation' => 'ml'],
            ['name' => 'Piece', 'abbreviation' => 'pc'],
            ['name' => 'Portion', 'abbreviation' => 'ptn'],
            ['name' => 'Box', 'abbreviation' => 'box'],
            ['name' => 'Bag', 'abbreviation' => 'bag'],
        ];

        $unit = fake()->unique()->randomElement($units);

        return [
            'name' => $unit['name'],
            'abbreviation' => $unit['abbreviation'],
        ];
    }
}
