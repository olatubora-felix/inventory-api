<?php

namespace App\Exceptions;

use App\Http\Responses\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ApiExceptionRenderer
{
    /**
     * Render API exceptions as JSON with consistent envelope (no HTML).
     */
    public function render(Throwable $e, Request $request): mixed
    {
        if (!$request->is('api/*')) {
            return null;
        }

        $response = match (true) {
            $e instanceof ValidationException => $this->renderValidation($e),
            $e instanceof AuthenticationException => ApiResponse::error('Unauthenticated.', 401),
            $e instanceof AuthorizationException => ApiResponse::error($e->getMessage() ?: 'Forbidden.', 403),
            $e instanceof ModelNotFoundException => ApiResponse::error('Resource not found.', 404),
            $e instanceof NotFoundHttpException => ApiResponse::error($e->getMessage() ?: 'Not found.', 404),
            $e instanceof HttpException => ApiResponse::error($e->getMessage() ?: 'Error.', $e->getStatusCode()),
            default => $this->renderServerError($e),
        };

        return $response;
    }

    private function renderValidation(ValidationException $e): JsonResponse
    {
        $message = $e->getMessage();
        if (empty($message)) {
            $message = 'Validation failed.';
        }

        $payload = [
            'success' => false,
            'message' => $message,
            'data' => ['errors' => $e->errors()],
            'errors' => $e->errors(),
        ];

        return response()->json($payload, 422);
    }

    private function renderServerError(Throwable $e): JsonResponse
    {
        $message = config('app.debug') ? $e->getMessage() : 'Server Error';
        $data = config('app.debug') ? [
            'exception' => $e::class,
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ] : null;

        return ApiResponse::error($message, 500, $data);
    }
}
