<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\StockMovementType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StockMovement\StoreStockMovementRequest;
use App\Http\Resources\Api\V1\StockMovementResource;
use App\Http\Responses\ApiResponse;
use App\Models\StockLevel;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockMovementController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/stock-movements",
     *     tags={"Stock Movements"},
     *     summary="List stock movements",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="product_id", in="query", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="type", in="query", required=false, @OA\Schema(type="string", enum={"purchase","consumption","adjustment","waste","return"})),
     *     @OA\Parameter(name="from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="to", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Paginated list of movements"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $movements = StockMovement::query()
            ->with(['product', 'user'])
            ->when($request->filled('product_id'), fn ($q) => $q->where('product_id', $request->get('product_id')))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->get('type')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('occurred_at', '>=', $request->get('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('occurred_at', '<=', $request->get('to')))
            ->latest('occurred_at')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success(StockMovementResource::collection($movements));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/stock-movements",
     *     tags={"Stock Movements"},
     *     summary="Record a new stock movement",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"product_id","type","quantity"},
     *
     *         @OA\Property(property="product_id", type="string", format="uuid"),
     *         @OA\Property(property="type", type="string", enum={"purchase","consumption","adjustment","waste","return"}),
     *         @OA\Property(property="quantity", type="number", minimum=0.001),
     *         @OA\Property(property="unit_cost", type="number", nullable=true),
     *         @OA\Property(property="reference_number", type="string", nullable=true),
     *         @OA\Property(property="notes", type="string", nullable=true),
     *         @OA\Property(property="occurred_at", type="string", format="date-time", nullable=true)
     *     )),
     *
     *     @OA\Response(response=201, description="Movement recorded"),
     *     @OA\Response(response=422, description="Validation error or insufficient stock"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function store(StoreStockMovementRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $type = StockMovementType::from($validated['type']);

        $movement = DB::transaction(function () use ($validated, $type, $request) {
            $stockLevel = StockLevel::query()
                ->lockForUpdate()
                ->firstOrCreate(
                    ['product_id' => $validated['product_id']],
                    ['quantity_on_hand' => 0]
                );

            $currentQty = (float) $stockLevel->quantity_on_hand;
            $quantity = (float) $validated['quantity'];

            if (! $type->increasesStock() && $currentQty < $quantity) {
                return null;
            }

            $newQty = $type->increasesStock()
                ? $currentQty + $quantity
                : $currentQty - $quantity;

            $stockLevel->update([
                'quantity_on_hand' => $newQty,
                'last_updated_at' => now(),
            ]);

            return StockMovement::query()->create([
                'product_id' => $validated['product_id'],
                'user_id' => $request->user()->id,
                'type' => $type,
                'quantity' => $quantity,
                'quantity_before' => $currentQty,
                'quantity_after' => $newQty,
                'unit_cost' => $validated['unit_cost'] ?? null,
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'occurred_at' => $validated['occurred_at'] ?? now(),
            ]);
        });

        if ($movement === null) {
            return ApiResponse::error('Insufficient stock for this movement.', 422);
        }

        return ApiResponse::success(new StockMovementResource($movement->load(['product', 'user'])), 'Movement recorded.', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/stock-movements/{stock_movement}",
     *     tags={"Stock Movements"},
     *     summary="Show a single stock movement",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="stock_movement", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *
     *     @OA\Response(response=200, description="Movement details"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(StockMovement $stockMovement): JsonResponse
    {
        return ApiResponse::success(new StockMovementResource($stockMovement->load(['product', 'user'])));
    }
}
