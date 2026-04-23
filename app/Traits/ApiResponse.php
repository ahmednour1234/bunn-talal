<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function successResponse(mixed $data = null, string $message = 'تمت العملية بنجاح', int $code = 200): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $data,
            'code'    => $code,
        ], $code);
    }

    protected function errorResponse(string $message, int $code = 400, mixed $errors = null): JsonResponse
    {
        $payload = [
            'status'  => false,
            'message' => $message,
            'data'    => null,
            'code'    => $code,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $code);
    }

    protected function validationErrorResponse(mixed $errors, string $message = 'خطأ في البيانات المدخلة'): JsonResponse
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
            'data'    => null,
            'code'    => 422,
            'errors'  => $errors,
        ], 422);
    }

    protected function unauthorizedResponse(string $message = 'غير مصرح لك بالوصول'): JsonResponse
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
            'data'    => null,
            'code'    => 401,
        ], 401);
    }

    protected function forbiddenResponse(string $message = 'ليس لديك صلاحية'): JsonResponse
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
            'data'    => null,
            'code'    => 403,
        ], 403);
    }

    protected function notFoundResponse(string $message = 'العنصر غير موجود'): JsonResponse
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
            'data'    => null,
            'code'    => 404,
        ], 404);
    }

    protected function serverErrorResponse(string $message = 'حدث خطأ في الخادم', int $code = 500): JsonResponse
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
            'data'    => null,
            'code'    => $code,
        ], $code);
    }
}
