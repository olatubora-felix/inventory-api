<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Supplier\StoreSupplierRequest;
use App\Http\Requests\Api\V1\Supplier\UpdateSupplierRequest;
use App\Http\Resources\Api\V1\SupplierResource;
use App\Http\Responses\ApiResponse;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;

class SupplierController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/suppliers",
     *     tags={"Suppliers"},
     *     summary="List all suppliers",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Paginated list of suppliers"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(): JsonResponse
    {
        $suppliers = Supplier::query()->paginate(request()->integer('per_page', 20));

        return ApiResponse::success(SupplierResource::collection($suppliers));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/suppliers",
     *     tags={"Suppliers"},
     *     summary="Create a supplier (admin only)",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"name"},
     *
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(property="contact_person", type="string", nullable=true),
     *         @OA\Property(property="email", type="string", format="email", nullable=true),
     *         @OA\Property(property="phone", type="string", nullable=true),
     *         @OA\Property(property="address", type="string", nullable=true),
     *         @OA\Property(property="is_active", type="boolean")
     *     )),
     *
     *     @OA\Response(response=201, description="Created"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(StoreSupplierRequest $request): JsonResponse
    {
        $supplier = Supplier::query()->create($request->validated());

        return ApiResponse::success(new SupplierResource($supplier), 'Supplier created.', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/suppliers/{supplier}",
     *     tags={"Suppliers"},
     *     summary="Show a supplier",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="supplier", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Supplier details"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(Supplier $supplier): JsonResponse
    {
        return ApiResponse::success(new SupplierResource($supplier));
    }

    /**
     * @OA\Put(
     *     path="/api/v1/suppliers/{supplier}",
     *     tags={"Suppliers"},
     *     summary="Update a supplier (admin only)",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="supplier", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"name"},
     *
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(property="contact_person", type="string", nullable=true),
     *         @OA\Property(property="email", type="string", format="email", nullable=true),
     *         @OA\Property(property="phone", type="string", nullable=true),
     *         @OA\Property(property="address", type="string", nullable=true),
     *         @OA\Property(property="is_active", type="boolean")
     *     )),
     *
     *     @OA\Response(response=200, description="Updated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier): JsonResponse
    {
        $supplier->update($request->validated());

        return ApiResponse::success(new SupplierResource($supplier->fresh()), 'Supplier updated.');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/suppliers/{supplier}",
     *     tags={"Suppliers"},
     *     summary="Soft-delete a supplier (admin only)",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="supplier", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=204, description="Deleted"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function destroy(Supplier $supplier): JsonResponse
    {
        $supplier->delete();

        return ApiResponse::success(null, 'Supplier deleted successfully.');
    }
}
