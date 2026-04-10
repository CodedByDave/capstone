<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        protected UserRepository $userRepo
    ) {}

    public function createOwner(array $data): User
    {
        return $this->userRepo->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => User::ROLE_OWNER,
        ]);
    }

    public function registerUser(array $data): User
    {
        return $this->userRepo->create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => 'user',
        ]);
    }
}

