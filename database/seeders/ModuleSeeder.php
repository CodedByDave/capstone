<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            [
                'name' => 'Employee Management',
                'description' => 'Manage employee profiles, roles, attendance, and payroll tracking.',
                'price' => 1800,
            ],
            [
                'name' => 'Inventory Management',
                'description' => 'Track supplies, stock levels, stock movements, and suppliers.',
                'price' => 2000,
            ],
            [
                'name' => 'Order Management',
                'description' => 'Create, track, and manage customer orders from processing to completion.',
                'price' => 2500,
            ],
            [
                'name' => 'Services & Pricing',
                'description' => 'Configure laundry services, pricing rules, discounts, and promos.',
                'price' => 1500,
            ],
            [
                'name' => 'Reports & Analytics',
                'description' => 'Generate sales reports, performance insights, and business analytics.',
                'price' => 2200,
            ],
        ];

        foreach ($modules as $module) {
            DB::table('modules')->updateOrInsert(
                ['name' => $module['name']],
                [
                    'description' => $module['description'],
                    'price' => $module['price'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
