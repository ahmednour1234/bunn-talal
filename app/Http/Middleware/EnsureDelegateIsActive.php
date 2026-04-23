<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDelegateIsActive
{
    use ApiResponse;

    public function handle(Request $request, Closure $next): Response
    {
        $delegate = $request->user();

        if (!$delegate || !$delegate->is_active) {
            return $this->forbiddenResponse('حسابك غير مفعّل، يرجى التواصل مع الإدارة');
        }

        return $next($request);
    }
}
