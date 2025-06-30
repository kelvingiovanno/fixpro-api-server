<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;

class ApiResponseService
{
    /**
     * Success Response (200 OK)
     *
     * @param mixed $data The data to return (optional)
     * @param string $message The success message
     * @return JsonResponse
     */
    public function ok(mixed $data = null, string $message = 'Success'): JsonResponse
    {
        return $this->customResponse($message, JsonResponse::HTTP_OK, $data);
    }

    /**
     * Created Response (201 Created)
     *
     * @param mixed $data The data to return (optional)
     * @param string $message The success message
     * @return JsonResponse
     */
    public function created(mixed $data = null, string $message = 'Resource created successfully'): JsonResponse
    {
        return $this->customResponse($message, JsonResponse::HTTP_CREATED, $data);
    }

    /**
     * Accepted Response (202 Accepted)
     *
     * @param mixed $data The data to return (optional)
     * @param string $message The success message
     * @return JsonResponse
     */
    public function accepted(mixed $data = null, string $message = 'Request accepted successfully'): JsonResponse
    {
        return $this->customResponse($message, JsonResponse::HTTP_ACCEPTED, $data);
    }

    /**
     * No Content Response (204 No Content)
     *
     * @param string $message The success message
     * @return JsonResponse
     */
    public function noContent(string $message = 'No content to return'): JsonResponse
    {
        return $this->customResponse($message, JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Bad Request Response (400 Bad Request)
     *
     * @param string $message The error message
     * @param mixed $errors The error details (optional)
     * @return JsonResponse
     */
    public function badRequest(string $message = 'Bad Request', mixed $errors = null): JsonResponse
    {
        return $this->customResponse($message, JsonResponse::HTTP_BAD_REQUEST, null, $errors);
    }

    /**
     * Unauthorized Response (401 Unauthorized)
     *
     * @param string $message The error message
     * @param mixed $errors The error details (optional)
     * @return JsonResponse
     */
    public function unauthorized(string $message = 'Unauthorized', mixed $errors = null): JsonResponse
    {
        return $this->customResponse($message, JsonResponse::HTTP_UNAUTHORIZED, null, $errors);
    }

    /**
     * Forbidden Response (403 Forbidden)
     *
     * @param string $message The error message
     * @param mixed $errors The error details (optional)
     * @return JsonResponse
     */
    public function forbidden(string $message = 'Forbidden', mixed $errors = null): JsonResponse
    {
        return $this->customResponse($message, JsonResponse::HTTP_FORBIDDEN, null, $errors);
    }

    /**
     * Not Found Response (404 Not Found)
     *
     * @param string $message The error message
     * @param mixed $errors The error details (optional)
     * @return JsonResponse
     */
    public function notFound(string $message = 'Not Found', mixed $errors = null): JsonResponse
    {
        return $this->customResponse($message, JsonResponse::HTTP_NOT_FOUND, null, $errors);
    }

    /**
     * Method Not Allowed Response (405 Method Not Allowed)
     *
     * @param string $message The error message
     * @param mixed $errors The error details (optional)
     * @return JsonResponse
     */
    public function methodNotAllowed(string $message = 'Method Not Allowed', mixed $errors = null): JsonResponse
    {
        return $this->customResponse($message, JsonResponse::HTTP_METHOD_NOT_ALLOWED, null, $errors);
    }

    /**
     * Unprocessable Entity Response (422 Unprocessable Entity)
     *
     * @param string $message The error message
     * @param mixed $errors The error details (optional)
     * @return JsonResponse
     */
    public function unprocessableEntity(string $message = 'Unprocessable Entity', mixed $errors = null): JsonResponse
    {
        return $this->customResponse($message, JsonResponse::HTTP_UNPROCESSABLE_ENTITY, null, $errors);
    }

    /**
     * Internal Server Error Response (500 Internal Server Error)
     *
     * @param string $message The error message
     * @param mixed $errors The error details (optional)
     * @return JsonResponse
     */
    public function internalServerError(string $message = 'Internal Server Error', mixed $errors = null): JsonResponse
    {
        return $this->customResponse($message, JsonResponse::HTTP_INTERNAL_SERVER_ERROR, null, app()->environment('production') ? null : $errors);
    }

    /**
     * Return raw binary content (e.g., PDF or image from memory)
     *
     * @param string $content The binary content
     * @param string $mimeType The MIME type (e.g., application/pdf)
     * @param string|null $fileName Optional: force download with this filename
     * @return \Illuminate\Http\Response
     */
    public function raw(string $content, string $mimeType, ?string $fileName = null)
    {
        $headers = ['Content-Type' => $mimeType];

        if ($fileName) {
            $headers['Content-Disposition'] = 'attachment; filename="' . $fileName . '"';
        }

        return response($content, 200, $headers);
    }


    /**
     * Custom Response - Can be used for any HTTP status
     *
     * @param string $message The message to send
     * @param int $code The HTTP status code
     * @param mixed $data The data to return (optional)
     * @param mixed $errors The errors to return (optional)
     * @return JsonResponse
     */
    private function customResponse(string $message, int $code, mixed $data = null, mixed $errors = null): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data'    => $data ?? new \stdClass(), // Default to an empty object if no data
            'errors'  => $errors ?? new \stdClass(), // Default to an empty object if no errors
        ], $code);
    }
}
