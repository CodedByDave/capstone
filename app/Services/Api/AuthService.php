<?php

namespace App\Services\Api;

use App\Repositories\UserRepository;

class AuthService
{
    protected UserRepository $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function register(array $data)
    {
        $user = $this->userRepo->create($data);

        return [
            'success' => true,
            'message' => 'User registered successfully',
            'data' => $user,
        ];
    }
}
