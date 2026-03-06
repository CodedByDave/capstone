<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\UpdatePermissionRequest;
use App\Http\Requests\Shop\ToggleEmployeeRoleRequest;
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

        $purchasedModules = $order ? $order->modules->map(fn($m) => [
            'name'  => $m->name,
            'price' => $m->price,
        ])->values() : [];

        $staff = Employee::where('shop_id', $shop->id)
            ->with('roles')
            ->get()
            ->map(fn($e) => [
                'id'    => $e->id,
                'name'  => $e->first_name . ' ' . $e->last_name,
                'email' => $e->email,
                'roles' => $e->roles->pluck('role')->values(),
            ]);

        $permissions = RolePermission::where('shop_id', $shop->id)
            ->get()
            ->groupBy('role')
            ->map(fn($items) => $items->groupBy('module')
                ->map(fn($actions) => $actions->pluck('action')->values())
            );

        // Load roles from DB
        $roles = ShopRole::where('shop_id', $shop->id)
            ->get()
            ->map(fn($r) => [
                'name'      => $r->name,
                'deletable' => !$r->is_default,
            ]);

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
        ]);
    }

    public function destroyRole(Request $request, string $roleName)
    {
        $shop = $this->getShop();

        $role = ShopRole::where('shop_id', $shop->id)
            ->where('name', $roleName)
            ->where('is_default', false)
            ->firstOrFail();

        // Remove from role_permissions
        RolePermission::where('shop_id', $shop->id)
            ->where('role', $roleName)
            ->delete();

        $role->delete();

        return response()->json(['success' => true]);
    }

    public function toggleEmployeeRole(ToggleEmployeeRoleRequest $request, $employeeId)
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
            if ($employee->roles()->count() <= 1) {
                return response()->json(['error' => 'Employee must have at least one role.'], 422);
            }
            $existing->delete();
            $has = false;
        } else {
            EmployeeRole::create([
                'employee_id' => $employee->id,
                'role'        => $data['role'],
            ]);
            $has = true;
        }

        return response()->json([
            'has'   => $has,
            'roles' => $employee->roles()->pluck('role')->values(),
        ]);
    }
}
