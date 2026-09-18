<?php

namespace App\Services;

use App\Enums\AccountType;
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
            'role' => AccountType::ShopOwner->value,
        ]);
    }

    public function registerUser(array $data): User
    {
        return $this->userRepo->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'] ?? \Illuminate\Support\Str::random(32)),
            'role' => AccountType::Customer->value,
            'google_id' => $data['google_id'] ?? null,
        ]);
    }
}
