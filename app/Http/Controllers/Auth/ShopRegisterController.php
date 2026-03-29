<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterShopRequest;
use App\Mail\OtpVerificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ShopRegisterController extends Controller
{
    public function show()
    {
        return Inertia::render('auth/RegisterShop', [
            'googleUser' => session('google_user'),
        ]);
    }

    public function store(RegisterShopRequest $request)
    {
        try {
            Log::info('Shop registration started', ['email' => $request->email]);

            // ── Google OAuth path — skip OTP, go straight to session ──
            if ($request->filled('google_id')) {
                session([
                    'pending_registration' => [
                        'name'         => $request->name,
                        'email'        => $request->email,
                        'password'     => $request->password,
                        'shop_name'    => $request->shop_name,
                        'phone'        => $request->phone,
                        'branch_name'  => $request->branch_name,
                        'block_street' => $request->block_street,
                        'municipality' => $request->municipality,
                        'barangay'     => $request->barangay,
                        'postal_code'  => $request->postal_code,
                        'google_id'    => $request->google_id,
                        'otp_verified' => true, // mark as already verified
                    ]
                ]);

                // Clear the google_user session — no longer needed
                session()->forget('google_user');

                Log::info('Google registration — OTP skipped', ['email' => $request->email]);

                return redirect()->route('otp.verify.page')
                    ->with('toast', [
                        'type'    => 'success',
                        'message' => 'Google account verified. Completing your registration...',
                    ]);
            }

            // ── Standard path — generate and send OTP ──
            $otp = random_int(100000, 999999);

            session([
                'pending_registration' => [
                    'name'           => $request->name,
                    'email'          => $request->email,
                    'password'       => $request->password,
                    'shop_name'      => $request->shop_name,
                    'phone'          => $request->phone,
                    'branch_name'    => $request->branch_name,
                    'block_street'   => $request->block_street,
                    'municipality'   => $request->municipality,
                    'barangay'       => $request->barangay,
                    'postal_code'    => $request->postal_code,
                    'otp_code'       => $otp,
                    'otp_expires_at' => now()->addMinutes(10)->toDateTimeString(),
                ]
            ]);

            Log::info('Registration data stored in session', ['email' => $request->email]);

            try {
                Mail::to($request->email)->send(new OtpVerificationMail($otp, $request->name));
                Log::info('OTP email sent', ['email' => $request->email]);
            } catch (\Exception $e) {
                Log::error('Failed to send OTP email', [
                    'error' => $e->getMessage(),
                    'email' => $request->email,
                ]);

                session()->forget('pending_registration');

                return back()->withInput()->withErrors([
                    'email' => 'Failed to send verification email. Please try again.',
                ]);
            }

            return redirect()->route('otp.verify.page')
                ->with('toast', [
                    'type'    => 'success',
                    'message' => 'OTP has been sent to your email. Please verify your account.',
                ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error during shop registration', ['errors' => $e->errors()]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Shop registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withInput()->withErrors([
                'email' => 'Registration failed. Please try again.',
            ]);
        }
    }
}
