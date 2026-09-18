<?php

use App\Enums\AccountType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $managerEmployeeIds = DB::table('employees')
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->where('users.role', 'manager')
            ->pluck('employees.id');

        $now = now();

        foreach ($managerEmployeeIds as $employeeId) {
            DB::table('employee_roles')->insertOrIgnore([
                'employee_id' => $employeeId,
                'role' => 'manager',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('users')
            ->where('role', 'manager')
            ->update(['role' => AccountType::Staff->value]);

        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE users MODIFY role ENUM('super_admin', 'owner', 'staff', 'user') NOT NULL DEFAULT 'user'"
            );
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE users MODIFY role ENUM('super_admin', 'owner', 'manager', 'staff', 'user') NOT NULL DEFAULT 'user'"
            );
        }
    }
};
