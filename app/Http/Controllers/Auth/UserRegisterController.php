<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserRequest;
use App\Services\UserService;
use Inertia\Inertia;

class UserRegisterController extends Controller
{
    public function __construct(private UserService $userService) {}

    /**
     * Show shop registration page
     */
    public function create()
    {

    }

    /**
     * Store shop and owner
     */
    public function store(RegisterUserRequest $request)
    {
        $this->userService->registerUser($request->validated());

        return redirect()->route('login')->with('toast', [
            'type' => 'success',
            'message' => 'Your account has been created. Please login.',
        ]);
    }
}
