<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterShopRequest;
use App\Services\ShopService;
use App\Mail\OtpVerificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ShopRegisterController extends Controller
{
    public function __construct(private ShopService $shopService) {}

    /**
     * Show shop registration page
     */
    public function create()
    {
        return Inertia::render('auth/RegisterShop');
    }

    /**
     * Store shop and owner
     */
    public function store(RegisterShopRequest $request)
    {
        $result = $this->shopService->registerShop($request->validated());

        $owner = $result['owner'];
        $shop = $result['shop'];

        // Generate OTP
        $otp = random_int(100000, 999999);
        $owner->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        // Send OTP email
        $shopName = \Illuminate\Support\Str::limit($shop->shop_name, 40);
        Mail::to($owner->email)->send(new \App\Mail\OtpVerificationMail($otp, $shopName));

        // Log the owner in
        auth()->login($owner);

        // Redirect to Verify OTP page
        return redirect()->route('otp.verify.page')
            ->with('toast', [
                'type' => 'success',
                'message' => 'OTP has been sent to your email. Please verify your account.'
            ]);
    }
}
