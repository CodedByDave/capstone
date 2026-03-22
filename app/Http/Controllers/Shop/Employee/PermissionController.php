<?php

namespace App\Http\Controllers\Shop\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\Employee\UpdatePermissionRequest;
use App\Http\Requests\Shop\Employee\ToggleEmployeeRoleRequest;
use App\Models\Employee;
use App\Models\EmployeeRole;
use App\Models\Order;
use App\Models\RolePermission;
use App\Models\Shop;
use App\Models\ShopRole;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PermissionController extends Controller
{
    private const RESERVED_ROLES = ['owner', 'super_admin'];

    private function getShop(): Shop
    {
        return Shop::where('owner_id', auth()->id())->firstOrFail();
    }

    public function index()
    {
        $shop  = $this->getShop();

        $order = Order::where('user_id', $shop->owner_id)
            ->where('status', 'paid')
            ->with('modules')
            ->latest()
            ->first();

        $purchasedModules = $order
            ? $order->modules
                ->map(fn ($m) => [
                    'name'  => $m->name,
                    'price' => $m->price,
                ])
                ->values()
                ->toArray()
            : [];

        $staff = Employee::where('shop_id', $shop->id)
            ->with('roles')
            ->get()
            ->map(fn ($e) => [
                'id'    => $e->id,
                'name'  => "{$e->first_name} {$e->last_name}",
                'email' => $e->email,
                'roles' => $e->roles->pluck('role')->values()->toArray(),
            ])
            ->toArray();

        $permissions = RolePermission::where('shop_id', $shop->id)
            ->get()
            ->groupBy('role')
            ->map(
                fn ($items) => $items
                    ->groupBy('module')
                    ->map(fn ($actions) => $actions->pluck('action')->unique()->values()->toArray())
                    ->toArray()
            )
            ->toArray();

        $roles = ShopRole::where('shop_id', $shop->id)
            ->get()
            ->map(fn ($r) => [
                'name'      => $r->name,
                'deletable' => ! $r->is_default,
            ])
            ->toArray();

        return Inertia::render('shop/permission/Index', [
            'purchasedModules' => $purchasedModules,
            'staff'            => $staff,
            'permissions'      => $permissions,
            'roles'            => $roles,
        ]);
    }

    public function updatePermission(UpdatePermissionRequest $request)
    {
        $shop = $this->getShop();
        $data = $request->validated();

        $existing = RolePermission::where([
            'shop_id' => $shop->id,
            'role'    => $data['role'],
            'module'  => $data['module'],
            'action'  => $data['action'],
        ])->first();

        if ($existing) {
            $existing->delete();
            $has = false;
        } else {
            RolePermission::create([
                'shop_id' => $shop->id,
                'role'    => $data['role'],
                'module'  => $data['module'],
                'action'  => $data['action'],
            ]);
            $has = true;
        }

        return response()->json(['has' => $has]);
    }

    public function storeRole(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:50'],
        ]);

        $shop = $this->getShop();
        $name = strtolower(trim($request->name));

        // Block reserved system role names
        if (in_array($name, self::RESERVED_ROLES)) {
            return response()->json(['error' => 'That role name is reserved.'], 422);
        }

        if (ShopRole::where('shop_id', $shop->id)->where('name', $name)->exists()) {
            return response()->json(['error' => 'Role already exists.'], 422);
        }

        $role = ShopRole::create([
            'shop_id'    => $shop->id,
            'name'       => $name,
            'is_default' => false,
        ]);

        return response()->json([
            'name'      => $role->name,
            'deletable' => true,
        ], 201);
    }

    public function destroyRole(Request $request, string $roleName)
    {
        $shop = $this->getShop();

        $role = ShopRole::where('shop_id', $shop->id)
            ->where('name', $roleName)
            ->where('is_default', false)
            ->firstOrFail();

        // Clean up role permissions for this role
        RolePermission::where('shop_id', $shop->id)
            ->where('role', $roleName)
            ->delete();

        // Clean up employee role assignments for this role
        EmployeeRole::whereHas('employee', fn ($q) => $q->where('shop_id', $shop->id))
            ->where('role', $roleName)
            ->delete();

        $role->delete();

        return response()->json(['success' => true]);
    }

    public function toggleEmployeeRole(ToggleEmployeeRoleRequest $request, int $employeeId)
    {
        $shop     = $this->getShop();
        $data     = $request->validated();

        $employee = Employee::where('id', $employeeId)
            ->where('shop_id', $shop->id)
            ->firstOrFail();

        $existing = EmployeeRole::where([
            'employee_id' => $employee->id,
            'role'        => $data['role'],
        ])->first();

        if ($existing) {
            // Prevent removing the last role
            if ($employee->roles()->count() <= 1) {
                return response()->json(
                    ['error' => 'Employee must have at least one role.'],
                    422
                );
            }

            $existing->delete();
            $has = false;
        } else {
            // Ensure the role actually belongs to this shop before assigning
            $roleExists = ShopRole::where('shop_id', $shop->id)
                ->where('name', $data['role'])
                ->exists();

            if (! $roleExists) {
                return response()->json(
                    ['error' => 'Role does not exist for this shop.'],
                    422
                );
            }

            EmployeeRole::create([
                'employee_id' => $employee->id,
                'role'        => $data['role'],
            ]);

            $has = true;
        }

        return response()->json([
            'has'   => $has,
            'roles' => $employee->roles()->pluck('role')->values()->toArray(),
        ]);
    }
}
