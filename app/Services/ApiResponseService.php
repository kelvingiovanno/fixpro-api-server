<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;

class ApiResponseService
{
    /**
     * Success Response
     */
    public function success(mixed $data = null, string $message = 'Success', int $status = 200): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'code'    => $status,
            'data'    => $data ?? (object)[] 
        ], $status);
    }

    /**
     * Error Response
     */
    public function error(string $message = 'Error', int $status = 400, mixed $errors = null): JsonResponse
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
            'code'    => $status,
            'errors'  => $errors ?? (object)[] 
        ], $status);
    }

    /**
     * Paginated Response (for lists)
     */
    public function paginated($data, string $message = 'Success', int $status = 200): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'code'    => $status,
            'data'    => $data->items(),
            'meta'    => [
                'current_page' => $data->currentPage(),
                'per_page'     => $data->perPage(),
                'total'        => $data->total(),
                'last_page'    => $data->lastPage()
            ]
        ], $status);
    }
}
