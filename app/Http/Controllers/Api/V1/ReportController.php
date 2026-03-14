<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/reports/inventory-summary",
     *     tags={"Reports"},
     *     summary="Inventory summary by category (admin only)",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(response=200, description="Summary data"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function summary(): JsonResponse
    {
        $totalProducts = Product::query()->count();
        $activeProducts = Product::query()->active()->count();

        $lowStockCount = Product::query()
            ->join('stock_levels', 'products.id', '=', 'stock_levels.product_id')
            ->whereColumn('stock_levels.quantity_on_hand', '<=', 'products.reorder_level')
            ->count();

        $byCategory = Category::query()
            ->withCount('products')
            ->get()
            ->map(fn ($cat) => [
                'category' => $cat->name,
                'product_count' => $cat->products_count,
            ]);

        return ApiResponse::success([
            'total_products' => $totalProducts,
            'active_products' => $activeProducts,
            'low_stock_count' => $lowStockCount,
            'by_category' => $byCategory,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/reports/stock-value",
     *     tags={"Reports"},
     *     summary="Total stock value report (admin only)",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(response=200, description="Stock value data"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function stockValue(): JsonResponse
    {
        $items = DB::table('products')
            ->join('stock_levels', 'products.id', '=', 'stock_levels.product_id')
            ->whereNull('products.deleted_at')
            ->select([
                'products.id',
                'products.name',
                'products.sku',
                'products.cost_price',
                'stock_levels.quantity_on_hand',
                DB::raw('COALESCE(products.cost_price, 0) * stock_levels.quantity_on_hand AS total_value'),
            ])
            ->orderByDesc('total_value')
            ->get();

        $grandTotal = $items->sum('total_value');

        return ApiResponse::success([
            'items' => $items,
            'grand_total' => round($grandTotal, 2),
        ]);
    }
}
