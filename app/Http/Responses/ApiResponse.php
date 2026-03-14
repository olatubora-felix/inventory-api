<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;

class ApiResponse
{
    /**
     * Return a successful JSON response with consistent envelope.
     *
     * @param  array<string, mixed>|JsonResource|ResourceCollection|AbstractPaginator|AbstractCursorPaginator|null  $data
     */
    public static function success(
        array|JsonResource|ResourceCollection|AbstractPaginator|AbstractCursorPaginator|null $data = null,
        string $message = 'Success',
        int $status = 200
    ): JsonResponse {
        $payload = [
            'success' => true,
            'message' => $message,
            'data' => self::normalizeData($data),
        ];

        return response()->json($payload, $status);
    }

    /**
     * Return an error JSON response with consistent envelope.
     *
     * @param  array<string, mixed>|object|null  $data
     */
    public static function error(
        string $message = 'An error occurred',
        int $status = 400,
        array|object|null $data = null
    ): JsonResponse {
        $payload = [
            'success' => false,
            'message' => $message,
            'data' => $data,
        ];

        return response()->json($payload, $status);
    }

    /**
     * Normalize resource/pagination into array for the data key.
     *
     * @param  array<string, mixed>|JsonResource|ResourceCollection|AbstractPaginator|AbstractCursorPaginator|null  $data
     * @return array<string, mixed>|object|null
     */
    private static function normalizeData(
        array|JsonResource|ResourceCollection|AbstractPaginator|AbstractCursorPaginator|null $data
    ): array|object|null {
        if ($data === null) {
            return null;
        }

        if ($data instanceof JsonResource) {
            return $data->resolve();
        }

        if ($data instanceof ResourceCollection) {
            $request = request();
            $items = $data->resolve($request);
            $items = is_array($items) ? $items : (method_exists($items, 'all') ? $items->all() : $items);
            $resource = $data->resource;
            if ($resource instanceof AbstractPaginator || $resource instanceof AbstractCursorPaginator) {
                $paginatorArray = $resource->toArray();

                return [
                    'data' => $items,
                    'meta' => [
                        'current_page' => $paginatorArray['current_page'] ?? null,
                        'from' => $paginatorArray['from'] ?? null,
                        'last_page' => $paginatorArray['last_page'] ?? null,
                        'path' => $paginatorArray['path'] ?? null,
                        'per_page' => $paginatorArray['per_page'] ?? null,
                        'to' => $paginatorArray['to'] ?? null,
                        'total' => $paginatorArray['total'] ?? null,
                    ],
                    'links' => $paginatorArray['links'] ?? [],
                ];
            }

            return ['data' => $items];
        }

        if ($data instanceof AbstractPaginator || $data instanceof AbstractCursorPaginator) {
            return $data->toArray();
        }

        return $data;
    }
}
