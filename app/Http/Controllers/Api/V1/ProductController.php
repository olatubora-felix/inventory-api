<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Product\StoreProductRequest;
use App\Http\Requests\Api\V1\Product\UpdateProductRequest;
use App\Http\Resources\Api\V1\ProductResource;
use App\Http\Responses\ApiResponse;
use App\Models\Product;
use App\Models\StockLevel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/products",
     *     tags={"Products"},
     *     summary="List all products",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="category_id", in="query", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="supplier_id", in="query", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Paginated list of products"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $products = Product::query()
            ->with(['category', 'unitOfMeasure', 'stockLevel'])
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->get('category_id')))
            ->when($request->filled('supplier_id'), fn ($q) => $q->whereHas('suppliers', fn ($s) => $s->where('suppliers.id', $request->get('supplier_id'))))
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($w) => $w->whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($request->get('search')).'%'])->orWhereRaw('LOWER(sku) LIKE ?', ['%'.strtolower($request->get('search')).'%'])))
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success(ProductResource::collection($products));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/products",
     *     tags={"Products"},
     *     summary="Create a product (admin only)",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"name","sku","category_id","unit_of_measure_id","reorder_level"},
     *
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(property="sku", type="string"),
     *         @OA\Property(property="description", type="string", nullable=true),
     *         @OA\Property(property="category_id", type="string", format="uuid"),
     *         @OA\Property(property="unit_of_measure_id", type="string", format="uuid"),
     *         @OA\Property(property="reorder_level", type="number"),
     *         @OA\Property(property="cost_price", type="number", nullable=true),
     *         @OA\Property(property="is_active", type="boolean"),
     *         @OA\Property(property="supplier_ids", type="array", @OA\Items(type="string", format="uuid"))
     *     )),
     *
     *     @OA\Response(response=201, description="Product created"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $supplierIds = $validated['supplier_ids'] ?? [];
        unset($validated['supplier_ids']);

        $product = Product::query()->create($validated);

        if ($supplierIds) {
            $product->suppliers()->sync($supplierIds);
        }

        StockLevel::query()->create(['product_id' => $product->id, 'quantity_on_hand' => 0]);

        return ApiResponse::success(new ProductResource($product->load(['category', 'unitOfMeasure', 'stockLevel', 'suppliers'])), 'Product created.', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/products/{product}",
     *     tags={"Products"},
     *     summary="Show a product",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="product", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *
     *     @OA\Response(response=200, description="Product details"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(Product $product): JsonResponse
    {
        return ApiResponse::success(new ProductResource($product->load(['category', 'unitOfMeasure', 'stockLevel', 'suppliers'])));
    }

    /**
     * @OA\Put(
     *     path="/api/v1/products/{product}",
     *     tags={"Products"},
     *     summary="Update a product (admin only)",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="product", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"name","sku","category_id","unit_of_measure_id","reorder_level"},
     *
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(property="sku", type="string"),
     *         @OA\Property(property="category_id", type="string", format="uuid"),
     *         @OA\Property(property="unit_of_measure_id", type="string", format="uuid"),
     *         @OA\Property(property="reorder_level", type="number"),
     *         @OA\Property(property="cost_price", type="number", nullable=true),
     *         @OA\Property(property="is_active", type="boolean"),
     *         @OA\Property(property="supplier_ids", type="array", @OA\Items(type="string", format="uuid"))
     *     )),
     *
     *     @OA\Response(response=200, description="Updated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $validated = $request->validated();
        $supplierIds = $validated['supplier_ids'] ?? null;
        unset($validated['supplier_ids']);

        $product->update($validated);

        if ($supplierIds !== null) {
            $product->suppliers()->sync($supplierIds);
        }

        return ApiResponse::success(new ProductResource($product->load(['category', 'unitOfMeasure', 'stockLevel', 'suppliers'])), 'Product updated.');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/products/{product}",
     *     tags={"Products"},
     *     summary="Soft-delete a product (admin only)",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="product", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *
     *     @OA\Response(response=204, description="Deleted"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return ApiResponse::success(null, 'Product deleted successfully.');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/products/low-stock",
     *     tags={"Products"},
     *     summary="List products at or below their reorder level",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(response=200, description="Low-stock products"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function lowStock(): JsonResponse
    {
        $products = Product::query()
            ->with(['category', 'unitOfMeasure', 'stockLevel'])
            ->lowStock()
            ->select('products.*')
            ->paginate(20);

        return ApiResponse::success(ProductResource::collection($products));
    }
}
