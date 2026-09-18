<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if super admin already exists
        $superAdminEmail = 'admin@example.com';

        $user = User::firstOrCreate(
            ['email' => $superAdminEmail],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('AdminPassword_123'),
                'role' => AccountType::SuperAdmin->value,
                'email_verified_at' => now(),
            ]
        );

        if ($user->email_verified_at === null) {
            $user->update(['email_verified_at' => now()]);
        }

        $this->command->info('Super admin created or already exists: '.$superAdminEmail);
    }
}
