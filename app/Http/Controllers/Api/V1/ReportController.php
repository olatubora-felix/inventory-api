<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\StockMovementResource;
use App\Http\Responses\ApiResponse;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/dashboard/stats",
     *     tags={"Dashboard"},
     *     summary="Key dashboard stats (total products, stock value, low stock items, total quantity)",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(response=200, description="Dashboard stats"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function dashboardStats(): JsonResponse
    {
        $totalProducts = Product::query()->count();

        $stockAggregates = DB::table('stock_levels')
            ->join('products', 'stock_levels.product_id', '=', 'products.id')
            ->whereNull('products.deleted_at')
            ->selectRaw('
                SUM(COALESCE(products.cost_price, 0) * stock_levels.quantity_on_hand) AS total_stock_value,
                SUM(stock_levels.quantity_on_hand) AS total_quantity
            ')
            ->first();

        $lowStockItems = Product::query()
            ->lowStock()
            ->count();

        return ApiResponse::success([
            'total_products' => $totalProducts,
            'total_stock_value' => round((float) ($stockAggregates->total_stock_value ?? 0), 2),
            'low_stock_items' => $lowStockItems,
            'total_quantity' => (float) ($stockAggregates->total_quantity ?? 0),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/dashboard/recent-stock-movements",
     *     tags={"Dashboard"},
     *     summary="Recent stock movements",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="limit", in="query", required=false, @OA\Schema(type="integer", default=10)),
     *
     *     @OA\Response(response=200, description="Recent stock movements"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function recentStockMovements(): JsonResponse
    {
        $limit = min(request()->integer('limit', 10), 50);

        $movements = StockMovement::query()
            ->with(['product:id,name,sku', 'user:id,name'])
            ->latest('occurred_at')
            ->limit($limit)
            ->get();

        return ApiResponse::success(StockMovementResource::collection($movements)->resolve());
    }

    /**
     * @OA\Get(
     *     path="/api/v1/dashboard/top-categories",
     *     tags={"Dashboard"},
     *     summary="Top categories ranked by total stock value",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="limit", in="query", required=false, @OA\Schema(type="integer", default=5)),
     *
     *     @OA\Response(response=200, description="Top categories by stock value"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function topCategoriesByValue(): JsonResponse
    {
        $limit = min(request()->integer('limit', 5), 20);

        $categories = DB::table('categories')
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->join('stock_levels', 'products.id', '=', 'stock_levels.product_id')
            ->whereNull('products.deleted_at')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_value')
            ->limit($limit)
            ->select([
                'categories.id',
                'categories.name',
                DB::raw('COUNT(DISTINCT products.id) AS product_count'),
                DB::raw('SUM(stock_levels.quantity_on_hand) AS total_quantity'),
                DB::raw('SUM(COALESCE(products.cost_price, 0) * stock_levels.quantity_on_hand) AS total_value'),
            ])
            ->get()
            ->map(fn ($row) => [
                'id' => $row->id,
                'name' => $row->name,
                'product_count' => (int) $row->product_count,
                'total_quantity' => (float) $row->total_quantity,
                'total_value' => round((float) $row->total_value, 2),
            ]);

        return ApiResponse::success($categories->all());
    }

    /**
     * @OA\Get(
     *     path="/api/v1/dashboard/inventory-by-category",
     *     tags={"Dashboard"},
     *     summary="Inventory breakdown by category",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(response=200, description="Inventory breakdown by category"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function inventoryByCategory(): JsonResponse
    {
        $breakdown = DB::table('categories')
            ->leftJoin('products', function ($join) {
                $join->on('categories.id', '=', 'products.category_id')
                    ->whereNull('products.deleted_at');
            })
            ->leftJoin('stock_levels', 'products.id', '=', 'stock_levels.product_id')
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('categories.name')
            ->select([
                'categories.id',
                'categories.name',
                DB::raw('COUNT(DISTINCT products.id) AS total_products'),
                DB::raw('COUNT(DISTINCT CASE WHEN products.is_active = 1 THEN products.id END) AS active_products'),
                DB::raw('COALESCE(SUM(stock_levels.quantity_on_hand), 0) AS total_quantity'),
                DB::raw('COALESCE(SUM(COALESCE(products.cost_price, 0) * stock_levels.quantity_on_hand), 0) AS total_value'),
                DB::raw('COUNT(DISTINCT CASE WHEN stock_levels.quantity_on_hand <= products.reorder_level THEN products.id END) AS low_stock_count'),
            ])
            ->get()
            ->map(fn ($row) => [
                'id' => $row->id,
                'name' => $row->name,
                'total_products' => (int) $row->total_products,
                'active_products' => (int) $row->active_products,
                'total_quantity' => (float) $row->total_quantity,
                'total_value' => round((float) $row->total_value, 2),
                'low_stock_count' => (int) $row->low_stock_count,
            ]);

        return ApiResponse::success($breakdown->all());
    }

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
