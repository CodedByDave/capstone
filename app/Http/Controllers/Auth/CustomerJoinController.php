<?php

namespace App\Http\Controllers\Auth;

use App\Enums\AccountType;
use App\Http\Controllers\Controller;
use App\Services\CustomerReferralService;
use Illuminate\Http\RedirectResponse;

class CustomerJoinController extends Controller
{
    public function __construct(
        private readonly CustomerReferralService $referrals,
    ) {}

    /** Landing point of a shop's QR code. */
    public function show(string $publicId): RedirectResponse
    {
        $shop = $this->referrals->findJoinableShop($publicId);

        if (! $shop) {
            return redirect()->route('login')->with('toast', [
                'type' => 'error',
                'message' => 'This shop QR code is not valid or the shop is not accepting customers right now.',
            ]);
        }

        $user = auth()->user();

        if ($user === null) {
            session(['referral_shop_id' => $shop->id]);

            return redirect()->route('register.user');
        }

        if ($user->role !== AccountType::Customer->value) {
            return redirect()->route(AccountType::dashboardRouteFor($user->role))->with('toast', [
                'type' => 'info',
                'message' => 'This link is for customers. Log out, then open it again to sign up as a customer.',
            ]);
        }

        $joined = $this->referrals->bindCustomer($user, $shop);

        return redirect()->route('user.dashboard')->with('toast', $joined
            ? ['type' => 'success', 'message' => "You are now connected to {$shop->shop_name}."]
            : ['type' => 'warning', 'message' => 'Your account is already connected to another shop.']);
    }
}
