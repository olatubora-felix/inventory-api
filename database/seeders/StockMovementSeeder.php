<?php

namespace Database\Seeders;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockLevel;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockMovementSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@inventory.test')->first();
        $products = Product::query()->get();

        foreach ($products as $product) {
            $openingQty = fake()->randomFloat(3, 20, 200);

            DB::transaction(function () use ($product, $admin, $openingQty): void {
                StockMovement::query()->create([
                    'product_id' => $product->id,
                    'user_id' => $admin->id,
                    'type' => StockMovementType::Purchase,
                    'quantity' => $openingQty,
                    'quantity_before' => 0,
                    'quantity_after' => $openingQty,
                    'reference_number' => 'OPEN-STOCK',
                    'notes' => 'Opening stock',
                    'occurred_at' => now()->subDays(30),
                ]);

                StockLevel::query()->updateOrCreate(
                    ['product_id' => $product->id],
                    ['quantity_on_hand' => $openingQty, 'last_updated_at' => now()]
                );
            });
        }

        $productIds = $products->pluck('id')->all();
        $userIds = User::query()->pluck('id')->all();
        $consumptionTypes = [StockMovementType::Consumption, StockMovementType::Waste, StockMovementType::Adjustment];

        for ($i = 0; $i < 50; $i++) {
            $productId = fake()->randomElement($productIds);
            $stockLevel = StockLevel::query()->where('product_id', $productId)->first();
            $currentQty = (float) $stockLevel->quantity_on_hand;
            $qty = fake()->randomFloat(3, 0.1, min(10, $currentQty));

            if ($qty <= 0 || $currentQty <= 0) {
                continue;
            }

            DB::transaction(function () use ($productId, $userIds, $consumptionTypes, $currentQty, $qty, $stockLevel): void {
                $type = fake()->randomElement($consumptionTypes);
                $newQty = max(0, $currentQty - $qty);

                StockMovement::query()->create([
                    'product_id' => $productId,
                    'user_id' => fake()->randomElement($userIds),
                    'type' => $type,
                    'quantity' => $qty,
                    'quantity_before' => $currentQty,
                    'quantity_after' => $newQty,
                    'occurred_at' => fake()->dateTimeBetween('-29 days', 'now'),
                ]);

                $stockLevel->update(['quantity_on_hand' => $newQty, 'last_updated_at' => now()]);
            });
        }
    }
}
