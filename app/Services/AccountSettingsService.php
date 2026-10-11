<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;

class AccountSettingsService
{
    public function __construct(private readonly UserRepository $users) {}

    public function updatePassword(User $user, string $password): void
    {
        $this->users->updatePassword($user, $password);
    }
}
