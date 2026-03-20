<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Category\StoreCategoryRequest;
use App\Http\Requests\Api\V1\Category\UpdateCategoryRequest;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Http\Responses\ApiResponse;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/categories",
     *     tags={"Categories"},
     *     summary="List all categories",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(response=200, description="Paginated list of categories"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(): JsonResponse
    {
        $categories = Category::query()->withCount('products')->paginate(20);

        return ApiResponse::success(CategoryResource::collection($categories));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/categories",
     *     tags={"Categories"},
     *     summary="Create a new category (admin only)",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"name"},
     *
     *         @OA\Property(property="name", type="string", example="Proteins"),
     *         @OA\Property(property="description", type="string", nullable=true)
     *     )),
     *
     *     @OA\Response(response=201, description="Category created"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = Category::query()->create($request->validated());

        return ApiResponse::success(new CategoryResource($category), 'Category created.', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/categories/{category}",
     *     tags={"Categories"},
     *     summary="Show a single category",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="category", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *
     *     @OA\Response(response=200, description="Category details"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(Category $category): JsonResponse
    {
        return ApiResponse::success(new CategoryResource($category->loadCount('products')));
    }

    /**
     * @OA\Put(
     *     path="/api/v1/categories/{category}",
     *     tags={"Categories"},
     *     summary="Update a category (admin only)",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="category", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"name"},
     *
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(property="description", type="string", nullable=true)
     *     )),
     *
     *     @OA\Response(response=200, description="Category updated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $category->update($request->validated());

        return ApiResponse::success(new CategoryResource($category->loadCount('products')), 'Category updated.');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/categories/{category}",
     *     tags={"Categories"},
     *     summary="Delete a category (admin only)",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="category", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *
     *     @OA\Response(response=204, description="Deleted"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return ApiResponse::success(null, 'Category deleted successfully.');
    }
}
