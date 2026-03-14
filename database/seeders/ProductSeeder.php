<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\UnitOfMeasure;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $categories = Category::query()->pluck('id')->all();
        $unitsByName = UnitOfMeasure::query()->pluck('id', 'name');
        $suppliers = Supplier::query()->pluck('id')->all();

        $products = [
            ['name' => 'Chicken Breast', 'sku' => 'PRO-CHK-001', 'category' => 'Proteins', 'unit' => 'Kilogram', 'reorder_level' => 20, 'cost_price' => 1800],
            ['name' => 'Chicken Thigh', 'sku' => 'PRO-CHK-002', 'category' => 'Proteins', 'unit' => 'Kilogram', 'reorder_level' => 15, 'cost_price' => 1400],
            ['name' => 'Chicken Wings', 'sku' => 'PRO-CHK-003', 'category' => 'Proteins', 'unit' => 'Kilogram', 'reorder_level' => 10, 'cost_price' => 1600],
            ['name' => 'Chicken Drumstick', 'sku' => 'PRO-CHK-004', 'category' => 'Proteins', 'unit' => 'Kilogram', 'reorder_level' => 10, 'cost_price' => 1500],
            ['name' => 'Tomatoes', 'sku' => 'VEG-TOM-001', 'category' => 'Vegetables', 'unit' => 'Kilogram', 'reorder_level' => 5, 'cost_price' => 400],
            ['name' => 'Lettuce', 'sku' => 'VEG-LET-001', 'category' => 'Vegetables', 'unit' => 'Kilogram', 'reorder_level' => 3, 'cost_price' => 350],
            ['name' => 'Onions', 'sku' => 'VEG-ONI-001', 'category' => 'Vegetables', 'unit' => 'Kilogram', 'reorder_level' => 5, 'cost_price' => 300],
            ['name' => 'French Fries', 'sku' => 'GRN-FRY-001', 'category' => 'Grains & Starches', 'unit' => 'Kilogram', 'reorder_level' => 25, 'cost_price' => 900],
            ['name' => 'White Rice', 'sku' => 'GRN-RIC-001', 'category' => 'Grains & Starches', 'unit' => 'Kilogram', 'reorder_level' => 30, 'cost_price' => 700],
            ['name' => 'Burger Buns', 'sku' => 'GRN-BUN-001', 'category' => 'Grains & Starches', 'unit' => 'Piece', 'reorder_level' => 100, 'cost_price' => 80],
            ['name' => 'Pita Bread', 'sku' => 'GRN-PIT-001', 'category' => 'Grains & Starches', 'unit' => 'Piece', 'reorder_level' => 50, 'cost_price' => 90],
            ['name' => 'Ketchup', 'sku' => 'SAU-KET-001', 'category' => 'Sauces & Condiments', 'unit' => 'Liter', 'reorder_level' => 5, 'cost_price' => 600],
            ['name' => 'Mayonnaise', 'sku' => 'SAU-MAY-001', 'category' => 'Sauces & Condiments', 'unit' => 'Liter', 'reorder_level' => 5, 'cost_price' => 800],
            ['name' => 'Pepper Sauce', 'sku' => 'SAU-PEP-001', 'category' => 'Sauces & Condiments', 'unit' => 'Liter', 'reorder_level' => 3, 'cost_price' => 700],
            ['name' => 'Barbecue Sauce', 'sku' => 'SAU-BBQ-001', 'category' => 'Sauces & Condiments', 'unit' => 'Liter', 'reorder_level' => 3, 'cost_price' => 750],
            ['name' => 'Vegetable Oil', 'sku' => 'SAU-OIL-001', 'category' => 'Sauces & Condiments', 'unit' => 'Liter', 'reorder_level' => 20, 'cost_price' => 1200],
            ['name' => 'Coca-Cola 50cl', 'sku' => 'BEV-COC-001', 'category' => 'Beverages', 'unit' => 'Piece', 'reorder_level' => 48, 'cost_price' => 250],
            ['name' => 'Fanta 50cl', 'sku' => 'BEV-FAN-001', 'category' => 'Beverages', 'unit' => 'Piece', 'reorder_level' => 48, 'cost_price' => 250],
            ['name' => 'Sprite 50cl', 'sku' => 'BEV-SPR-001', 'category' => 'Beverages', 'unit' => 'Piece', 'reorder_level' => 24, 'cost_price' => 250],
            ['name' => 'Still Water 75cl', 'sku' => 'BEV-WAT-001', 'category' => 'Beverages', 'unit' => 'Piece', 'reorder_level' => 24, 'cost_price' => 150],
            ['name' => 'Fruit Juice 30cl', 'sku' => 'BEV-JUI-001', 'category' => 'Beverages', 'unit' => 'Piece', 'reorder_level' => 24, 'cost_price' => 200],
            ['name' => 'Takeaway Box Medium', 'sku' => 'PKG-BOX-001', 'category' => 'Packaging', 'unit' => 'Piece', 'reorder_level' => 200, 'cost_price' => 50],
            ['name' => 'Takeaway Box Large', 'sku' => 'PKG-BOX-002', 'category' => 'Packaging', 'unit' => 'Piece', 'reorder_level' => 200, 'cost_price' => 70],
            ['name' => 'Paper Bags', 'sku' => 'PKG-BAG-001', 'category' => 'Packaging', 'unit' => 'Piece', 'reorder_level' => 300, 'cost_price' => 30],
            ['name' => 'Napkins', 'sku' => 'PKG-NAP-001', 'category' => 'Packaging', 'unit' => 'Piece', 'reorder_level' => 500, 'cost_price' => 10],
            ['name' => 'Plastic Forks', 'sku' => 'PKG-FOR-001', 'category' => 'Packaging', 'unit' => 'Piece', 'reorder_level' => 200, 'cost_price' => 15],
            ['name' => 'Dish Soap', 'sku' => 'CLN-DSH-001', 'category' => 'Cleaning Supplies', 'unit' => 'Liter', 'reorder_level' => 5, 'cost_price' => 500],
            ['name' => 'Floor Cleaner', 'sku' => 'CLN-FLR-001', 'category' => 'Cleaning Supplies', 'unit' => 'Liter', 'reorder_level' => 5, 'cost_price' => 600],
            ['name' => 'Hand Sanitiser', 'sku' => 'CLN-SAN-001', 'category' => 'Cleaning Supplies', 'unit' => 'Liter', 'reorder_level' => 3, 'cost_price' => 800],
            ['name' => 'Gloves Box', 'sku' => 'CLN-GLV-001', 'category' => 'Cleaning Supplies', 'unit' => 'Box', 'reorder_level' => 5, 'cost_price' => 1500],
        ];

        $categoryIds = Category::query()->pluck('id', 'name');

        foreach ($products as $data) {
            $product = Product::query()->firstOrCreate(
                ['sku' => $data['sku']],
                [
                    'name' => $data['name'],
                    'category_id' => $categoryIds[$data['category']],
                    'unit_of_measure_id' => $unitsByName[$data['unit']],
                    'reorder_level' => $data['reorder_level'],
                    'cost_price' => $data['cost_price'],
                    'is_active' => true,
                ]
            );

            if ($suppliers && $product->wasRecentlyCreated) {
                $supplierIds = collect($suppliers)->random(min(2, count($suppliers)));
                $product->suppliers()->sync($supplierIds);
            }
        }
    }
}
