<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;

class ApiResponseService
{
    /**
     * Success Response
     */
    public function success(mixed $data = null, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        return $this->customResponse(true, $message, $statusCode, $data);
    }

    /**
     * Error Response
     */
    public function error(string $message = 'Error', int $status = 400, mixed $errors = null): JsonResponse
    {
        return $this->customResponse(false, $message, $status, null, $errors);
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

    /**
     * Custom Response - Can be used for any HTTP status
     */
    public function customResponse(bool $status, string $message, int $code, mixed $data = null, mixed $errors = null): JsonResponse
    {
        return response()->json([
            'status'  => $status,
            'message' => $message,
            'data'    => $data ?? (object)[],
            'errors'  => $errors ?? (object)[]
        ], $code);
    }
}
