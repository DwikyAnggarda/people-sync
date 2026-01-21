<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    /**
     * Return a success response.
     */
    protected function success(mixed $data = null, ?string $message = null, int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Return a success response with pagination meta.
     */
    protected function successWithPagination(mixed $data, array $meta, ?string $message = null): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => $meta,
        ], 200);
    }

    /**
     * Return an error response.
     */
    protected function error(string $message, ?array $errors = null, int $statusCode = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }

    /**
     * Return a validation error response.
     */
    protected function validationError(string $message, array $errors): JsonResponse
    {
        return $this->error($message, $errors, 422);
    }

    /**
     * Return a not found error response.
     */
    protected function notFound(string $message = 'Data tidak ditemukan'): JsonResponse
    {
        return $this->error($message, null, 404);
    }

    /**
     * Return an unauthorized error response.
     */
    protected function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->error($message, null, 401);
    }

    /**
     * Return a forbidden error response.
     */
    protected function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, null, 403);
    }

    /**
     * Get pagination meta from Laravel paginator.
     */
    protected function getPaginationMeta($paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }
}
