<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $categories = [
            ['name' => 'Proteins', 'description' => 'Chicken, beef, fish, and other protein sources'],
            ['name' => 'Vegetables', 'description' => 'Fresh and frozen vegetables'],
            ['name' => 'Grains & Starches', 'description' => 'Rice, fries, bread, and starchy foods'],
            ['name' => 'Sauces & Condiments', 'description' => 'Sauces, dressings, and flavor enhancers'],
            ['name' => 'Beverages', 'description' => 'Soft drinks, juices, and water'],
            ['name' => 'Packaging', 'description' => 'Boxes, bags, wraps, and containers'],
            ['name' => 'Cleaning Supplies', 'description' => 'Detergents, sanitisers, and cleaning equipment'],
        ];

        foreach ($categories as $category) {
            Category::query()->firstOrCreate(['name' => $category['name']], $category);
        }
    }
}
