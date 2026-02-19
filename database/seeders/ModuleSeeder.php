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
                'name' => 'CRM',
                'description' => 'Manage customer relationships, track orders and communication efficiently.',
                'price' => 1500,
            ],
            [
                'name' => 'Supply Chain',
                'description' => 'Monitor inventory, suppliers, and streamline procurement for your shop.',
                'price' => 2000,
            ],
            [
                'name' => 'Billing / Invoicing',
                'description' => 'Generate invoices, manage payments and billing for your customers.',
                'price' => 1200,
            ],
            [
                'name' => 'Employee Management',
                'description' => 'Track employee schedules, payroll, and attendance easily.',
                'price' => 1800,
            ],
            [
                'name' => 'Analytics / Reporting',
                'description' => 'Get insights and reports on shop performance, sales, and operations.',
                'price' => 2200,
            ],
            [
                'name' => 'Marketing',
                'description' => 'Tools for promotions, campaigns, and customer engagement.',
                'price' => 1300,
            ],
        ];

        foreach ($modules as $module) {
            DB::table('modules')->updateOrInsert(
                ['name' => $module['name']], // unique key
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
