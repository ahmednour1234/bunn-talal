<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'permission'       => \App\Http\Middleware\CheckPermission::class,
            'delegate.active'  => \App\Http\Middleware\EnsureDelegateIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Return JSON for all API errors instead of HTML

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status'  => false,
                    'message' => 'غير مصرح لك بالوصول، يرجى تسجيل الدخول',
                    'data'    => null,
                    'code'    => 401,
                ], 401);
            }
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status'  => false,
                    'message' => 'العنصر المطلوب غير موجود',
                    'data'    => null,
                    'code'    => 404,
                ], 404);
            }
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status'  => false,
                    'message' => 'خطأ في البيانات المدخلة',
                    'data'    => null,
                    'code'    => 422,
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status'  => false,
                    'message' => 'حدث خطأ في الخادم، يرجى المحاولة لاحقاً',
                    'data'    => null,
                    'code'    => 500,
                ], 500);
            }
        });
    })->create();
