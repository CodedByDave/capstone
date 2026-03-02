<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function show()
    {
        return Inertia::render('auth/Login', [
            'canRegister' => true,
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }
}
