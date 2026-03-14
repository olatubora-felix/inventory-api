<?php

namespace Database\Seeders;

use App\Models\UnitOfMeasure;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitOfMeasureSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
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

        foreach ($units as $unit) {
            UnitOfMeasure::query()->firstOrCreate(['name' => $unit['name']], $unit);
        }
    }
}
