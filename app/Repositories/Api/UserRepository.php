<?php

namespace App\Repositories\Api;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRepository
{
    public function create(array $data): User
    {
        return User::create([
            'name' => $data['first_name'] . ' ' . $data['last_name'],
            'email' => $data['email'],
            'phone_number' => $data['phone_number'],
            'role' => User::ROLE_USER,
            'password' => Hash::make($data['password']),
            'is_verified' => false,
        ]);
    }
}
