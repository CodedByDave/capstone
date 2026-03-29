<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Employee;
use App\Models\Order;
use App\Models\RolePermission;
use App\Models\Shop;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        return [
            ...parent::share($request),
            'flash' => [
                'checkout_url' => session('checkout_url'),
            ],

            'name'  => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],

            'auth' => [
                'user' => $request->user() ? [
                    'id'          => $request->user()->id,
                    'name'        => $request->user()->name,
                    'email'       => $request->user()->email,
                    'role'        => $request->user()->role,
                    'permissions' => $this->resolvePermissions($request),
                ] : null,
            ],

            'order' => function () use ($request) {
                $user = $request->user();
                if (!$user) return null;

                if ($user->role === 'owner') {
                    $order = Order::where('user_id', $user->id)
                        ->where('status', 'paid')
                        ->with('modules')
                        ->latest()
                        ->first();

                    return $order ? [
                        'status'  => $order->status,
                        'modules' => $order->modules->map(fn($m) => [
                            'name'  => $m->name,
                            'price' => $m->price,
                        ]),
                    ] : null;
                }

                if ($user->role === 'staff') {
                    $employee = Employee::where('user_id', $user->id)->first();
                    if (!$employee) return null;

                    $shop = Shop::find($employee->shop_id);
                    if (!$shop) return null;

                    $order = Order::where('user_id', $shop->owner_id)
                        ->where('status', 'approved')
                        ->with('modules')
                        ->latest()
                        ->first();

                    if (!$order) return null;

                    // Get roles for this staff member
                    $roles = $employee->roles->pluck('role')->toArray();

                    // Get only modules this staff has at least one permission for
                    $permittedModules = RolePermission::where('shop_id', $employee->shop_id)
                        ->whereIn('role', $roles)
                        ->pluck('module')
                        ->unique()
                        ->toArray();

                    return [
                        'status' => $order->status,
                        // Only return modules the staff has permissions for
                        'modules' => $order->modules
                            ->filter(fn($m) => in_array($m->name, $permittedModules))
                            ->map(fn($m) => [
                                'name'  => $m->name,
                                'price' => $m->price,
                            ])
                            ->values(),
                    ];
                }

                return null;
            },

            'sidebarOpen' => !$request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'toast'       => fn() => $request->session()->get('toast'),
        ];
    }

    /* ─── PRIVATE ────────────────────────────────────────────── */

    private function resolvePermissions(Request $request): array
    {
        $user = $request->user();

        // Owner has everything — no need to query
        if (!$user || $user->role !== 'staff') return [];

        $employee = Employee::where('user_id', $user->id)
            ->with('roles')
            ->first();

        if (!$employee) return [];

        $roles = $employee->roles->pluck('role')->toArray();

        if (empty($roles)) return [];

        return RolePermission::where('shop_id', $employee->shop_id)
            ->whereIn('role', $roles)
            ->get(['module', 'action'])
            ->groupBy('module')
            ->map(fn($items) => $items->pluck('action')->unique()->values()->toArray())
            ->toArray();
    }
}
