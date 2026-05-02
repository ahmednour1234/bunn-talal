<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\DelegateRepositoryInterface;
use App\Services\DelegateService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly DelegateRepositoryInterface $delegateRepository,
        private readonly DelegateService $delegateService,
    ) {}

    /**
     * Login
     *
     * Authenticate a delegate and receive a Bearer token.
     *
     * @group Authentication
     * @unauthenticated
     *
     * @bodyParam email string required The delegate's email address. Example: ahmed@example.com
     * @bodyParam password string required The delegate's password. Example: secret123
     *
     * @response 200 scenario="Success" {
     *   "status": true,
     *   "message": "تم تسجيل الدخول بنجاح",
     *   "data": {
     *     "token": "1|abc123...",
     *     "delegate": { "id": 1, "name": "أحمد", "email": "ahmed@example.com", "phone": "0501234567" }
     *   },
     *   "code": 200
     * }
     * @response 401 scenario="Wrong credentials" {
     *   "status": false,
     *   "message": "البريد الإلكتروني أو كلمة المرور غير صحيحة",
     *   "data": null,
     *   "code": 401
     * }
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $delegate = $this->delegateRepository->findByEmail($request->email);

        if (!$delegate || !Hash::check($request->password, $delegate->password)) {
            return $this->unauthorizedResponse('البريد الإلكتروني أو كلمة المرور غير صحيحة');
        }

        if (!$delegate->is_active) {
            return $this->forbiddenResponse('حسابك غير مفعّل، يرجى التواصل مع الإدارة');
        }

        $token = $delegate->createToken('delegate-token')->plainTextToken;

        return $this->successResponse([
            'token'    => $token,
            'delegate' => [
                'id'    => $delegate->id,
                'name'  => $delegate->name,
                'email' => $delegate->email,
                'phone' => $delegate->phone,
            ],
        ], 'تم تسجيل الدخول بنجاح');
    }

    /**
     * Logout
     *
     * Revoke the current access token.
     *
     * @group Authentication
     *
     * @response 200 {"status": true, "message": "تم تسجيل الخروج بنجاح", "data": null, "code": 200}
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'تم تسجيل الخروج بنجاح');
    }

    /**
     * Update Location
     *
     * Update the authenticated delegate's current GPS coordinates.
     *
     * @group Authentication
     *
     * @bodyParam latitude  number required The current latitude of the delegate.  Example: 15.352700
     * @bodyParam longitude number required The current longitude of the delegate. Example: 44.206200
     *
     * @response 200 scenario="Success" {
     *   "status": true,
     *   "message": "تم تحديث الموقع بنجاح",
     *   "data": null,
     *   "code": 200
     * }
     * @response 422 scenario="Validation error" {
     *   "status": false,
     *   "message": "The given data was invalid.",
     *   "data": null,
     *   "code": 422
     * }
     */
    public function updateLocation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude'  => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $this->delegateService->updateLocation(
            $request->user()->id,
            (float) $validated['latitude'],
            (float) $validated['longitude'],
        );

        return $this->successResponse(null, 'تم تحديث الموقع بنجاح');
    }
}
