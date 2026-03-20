<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UnitOfMeasure\StoreUnitOfMeasureRequest;
use App\Http\Requests\Api\V1\UnitOfMeasure\UpdateUnitOfMeasureRequest;
use App\Http\Resources\Api\V1\UnitOfMeasureResource;
use App\Http\Responses\ApiResponse;
use App\Models\UnitOfMeasure;
use Illuminate\Http\JsonResponse;

class UnitOfMeasureController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/units-of-measure",
     *     tags={"Units of Measure"},
     *     summary="List all units of measure",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(response=200, description="Paginated list"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(): JsonResponse
    {
        return ApiResponse::success(UnitOfMeasureResource::collection(UnitOfMeasure::query()->paginate(20)));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/units-of-measure",
     *     tags={"Units of Measure"},
     *     summary="Create a unit of measure (admin only)",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"name","abbreviation"},
     *
     *         @OA\Property(property="name", type="string", example="Kilogram"),
     *         @OA\Property(property="abbreviation", type="string", example="kg")
     *     )),
     *
     *     @OA\Response(response=201, description="Created"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(StoreUnitOfMeasureRequest $request): JsonResponse
    {
        $unit = UnitOfMeasure::query()->create($request->validated());

        return ApiResponse::success(new UnitOfMeasureResource($unit), 'Unit of measure created.', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/units-of-measure/{unit_of_measure}",
     *     tags={"Units of Measure"},
     *     summary="Show a unit of measure",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="unit_of_measure", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *
     *     @OA\Response(response=200, description="Unit details"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(UnitOfMeasure $unitOfMeasure): JsonResponse
    {
        return ApiResponse::success(new UnitOfMeasureResource($unitOfMeasure));
    }

    /**
     * @OA\Put(
     *     path="/api/v1/units-of-measure/{unit_of_measure}",
     *     tags={"Units of Measure"},
     *     summary="Update a unit of measure (admin only)",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="unit_of_measure", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"name","abbreviation"},
     *
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(property="abbreviation", type="string")
     *     )),
     *
     *     @OA\Response(response=200, description="Updated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(UpdateUnitOfMeasureRequest $request, UnitOfMeasure $unitOfMeasure): JsonResponse
    {
        $unitOfMeasure->update($request->validated());

        return ApiResponse::success(new UnitOfMeasureResource($unitOfMeasure), 'Unit of measure updated.');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/units-of-measure/{unit_of_measure}",
     *     tags={"Units of Measure"},
     *     summary="Delete a unit of measure (admin only)",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="unit_of_measure", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *
     *     @OA\Response(response=204, description="Deleted"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function destroy(UnitOfMeasure $unitOfMeasure): JsonResponse
    {
        if ($unitOfMeasure->products()->exists()) {
            return ApiResponse::error('Cannot delete a unit of measure that is assigned to products.', 409);
        }

        $unitOfMeasure->delete();

        return ApiResponse::success(null, 'Unit of measure deleted successfully.');
    }
}
