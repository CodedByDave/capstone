<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Employee;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $employees = [
            [
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'email' => 'washer@laundry.com',
                'position' => 'Washer',
                'salary' => 12000,
            ],
            [
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'email' => 'cashier@laundry.com',
                'position' => 'Cashier',
                'salary' => 14000,
            ],
            [
                'first_name' => 'Pedro',
                'last_name' => 'Reyes',
                'email' => 'manager@laundry.com',
                'position' => 'Manager',
                'salary' => 20000,
            ],
        ];

        foreach ($employees as $data) {

            // Create user login account
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['first_name'] . ' ' . $data['last_name'],
                    'password' => Hash::make('password123'), // default password
                    'role' => 'employee',
                ]
            );

            // Create employee profile
            Employee::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_id' => 'EMP-' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'phone' => '09123456789',
                    'position' => $data['position'],
                    'hire_date' => now(),
                    'salary' => $data['salary'],
                    'status' => 'Active',
                ]
            );
        }
    }
}
