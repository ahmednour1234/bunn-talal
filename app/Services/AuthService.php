<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function attempt(array $credentials): bool
    {
        return Auth::guard('admin')->attempt($credentials);
    }

    public function logout(): void
    {
        Auth::guard('admin')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }

    public function admin()
    {
        return Auth::guard('admin')->user();
    }
}
