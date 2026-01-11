<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
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
                'password' => Hash::make('password123'), // Change this to a strong password
                'role' => 'super_admin',
            ]
        );

        $this->command->info('Super admin created or already exists: ' . $superAdminEmail);
    }
}
