<?php

use App\Enums\AccountType;
use App\Models\User;
use App\Repositories\UserRepository;

test('user verification filters use email verified at', function () {
    $verifiedUser = User::factory()->create([
        'role' => AccountType::Customer->value,
        'email_verified_at' => now(),
    ]);

    $unverifiedUser = User::factory()->unverified()->create([
        'role' => AccountType::Customer->value,
    ]);

    $repository = app(UserRepository::class);

    expect($repository->getStats()['verified'])->toBe(1);

    expect($repository->getPaginated(['verified' => 'yes'])->pluck('id'))
        ->toContain($verifiedUser->id)
        ->not->toContain($unverifiedUser->id)
        ->and($repository->getPaginated(['verified' => 'no'])->pluck('id'))
        ->toContain($unverifiedUser->id)
        ->not->toContain($verifiedUser->id);
});
