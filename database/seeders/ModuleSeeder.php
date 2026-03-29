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
                'name' => 'HRM',
                'description' => 'Manage employee profiles, branches, attendance, roles & positions, and activity logs.',
                'price' => 1800,
            ],
            [
                'name' => 'Inventory Management',
                'description' => 'Track supplies, stock levels, stock movements, categories, and suppliers.',
                'price' => 2000,
            ],
            [
                'name' => 'Operations',
                'description' => 'Handle end-to-end order processing, service management, pricing rules, and promotions.',
                'price' => 2500,
            ],
            [
                'name' => 'Finance Management',
                'description' => 'Monitor income, expenses, payroll, and transactions with a full financial overview.',
                'price' => 2200,
            ],
            [
                'name' => 'Reports & Analytics',
                'description' => 'Generate sales, inventory, and finance reports with business insights and audit logs.',
                'price' => 1500,
            ],
        ];

        foreach ($modules as $module) {
            DB::table('modules')->updateOrInsert(
                ['name' => $module['name']],
                [
                    'description' => $module['description'],
                    'price'       => $module['price'],
                    'updated_at'  => now(),
                    'created_at'  => now(),
                ]
            );
        }
    }
}
