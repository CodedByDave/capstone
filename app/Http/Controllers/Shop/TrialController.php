<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class TrialController extends Controller
{
    private const TRIAL_DAYS    = 7;
    private const TRIAL_PLAN    = 'Standard';
    private const TRIAL_MODULES = [
        'HRM',
        'Operations',
        'Inventory Management',
        'Finance Management',
    ];

    public function show()
    {
        $user = auth()->user();

        // Already has active plan
        $hasActive = Order::where('user_id', $user->id)
            ->whereIn('status', ['approved', 'paid', 'pending'])
            ->exists();

        if ($hasActive) {
            return redirect()->route('shop.dashboard')->with('toast', [
                'type'    => 'info',
                'message' => 'You already have an active plan.',
            ]);
        }

        // Already used trial
        $hasUsedTrial = Order::where('user_id', $user->id)
            ->where('is_trial', true)
            ->exists();

        if ($hasUsedTrial) {
            return redirect()->route('plans')->with('toast', [
                'type'    => 'info',
                'message' => 'You have already used your free trial. Choose a plan to continue.',
            ]);
        }

        $shop = Shop::where('owner_id', $user->id)->first();

        return Inertia::render('shop/TrialStart', [
            'trialDays' => self::TRIAL_DAYS,
            'user' => [
                'name'  => $user->name,
                'email' => $user->email,
            ],
            'shop' => [
                'shop_name'    => $shop?->shop_name    ?? '',
                'phone'        => $shop?->phone        ?? '',
                'block_street' => $shop?->block_street ?? '',
                'municipality' => $shop?->municipality ?? '',
                'barangay'     => $shop?->barangay     ?? '',
                'postal_code'  => $shop?->postal_code  ?? '',
            ],
        ]);
    }

    public function start(Request $request)
    {
        $request->validate([
            'shop_name'    => 'required|string|min:2|max:255',
            'phone'        => 'required|string|min:10|max:20',
            'municipality' => 'required|string|max:255',
            'barangay'     => 'required|string|max:255',
            'block_street' => 'nullable|string|max:255',
            'postal_code'  => 'required|string|max:10',
        ]);

        $user = auth()->user();

        $hasActive = Order::where('user_id', $user->id)
            ->whereIn('status', ['approved', 'paid', 'pending'])
            ->exists();

        if ($hasActive) {
            return back()->with('toast', [
                'type'    => 'error',
                'message' => 'You already have an active plan.',
            ]);
        }

        $hasUsedTrial = Order::where('user_id', $user->id)
            ->where('is_trial', true)
            ->exists();

        if ($hasUsedTrial) {
            return redirect()->route('plans')->with('toast', [
                'type'    => 'info',
                'message' => 'You have already used your free trial. Choose a plan to continue.',
            ]);
        }

        try {
            DB::transaction(function () use ($request, $user) {
                $order = Order::create([
                    'user_id'        => $user->id,
                    'shop_name'      => $request->shop_name,
                    'owner_name'     => $user->name,
                    'email'          => $user->email,
                    'phone'          => $request->phone,
                    'block_street'   => $request->block_street,
                    'municipality'   => $request->municipality,
                    'barangay'       => $request->barangay,
                    'postal_code'    => $request->postal_code,
                    'plan_name'      => self::TRIAL_PLAN,
                    'billing_months' => 0,
                    'total_price'    => 0,
                    'status'         => 'approved',
                    'is_trial'       => true,
                    'payment_method' => null,
                    'expires_at'     => now()->addDays(self::TRIAL_DAYS),
                ]);

                foreach (self::TRIAL_MODULES as $moduleName) {
                    $order->modules()->create(['name' => $moduleName, 'price' => 0]);
                }

                Shop::withTrashed()->updateOrCreate(
                    ['owner_id' => $user->id],
                    [
                        'shop_name'    => $request->shop_name,
                        'phone'        => $request->phone,
                        'block_street' => $request->block_street,
                        'municipality' => $request->municipality,
                        'barangay'     => $request->barangay,
                        'postal_code'  => $request->postal_code,
                        'status'       => 'active',
                        'deleted_at'   => null,
                    ]
                );
            });

            Log::info('Free trial started', ['user_id' => $user->id, 'shop' => $request->shop_name]);

            return redirect()->route('shop.dashboard')->with('toast', [
                'type'    => 'success',
                'message' => 'Your 7-day free trial has started! Explore the platform.',
            ]);
        } catch (\Exception $e) {
            Log::error('Trial start failed', ['error' => $e->getMessage(), 'user_id' => $user->id]);

            return back()->with('toast', [
                'type'    => 'error',
                'message' => 'Something went wrong. Please try again.',
            ]);
        }
    }
}
