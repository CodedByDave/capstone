<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserRequest;
use App\Mail\OtpVerificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class UserRegisterController extends Controller
{
    /**
     * Show customer registration page
     */
    public function create()
    {
        $googleUser = session('google_user');

        return Inertia::render('auth/RegisterUser', [
            'googleUser' => $googleUser,
        ]);
    }

    /**
     * Store pending registration data and send OTP (or skip OTP for Google)
     */
    public function store(RegisterUserRequest $request)
    {
        try {
            Log::info('User registration started', ['email' => $request->email]);

            // ── Google OAuth path — skip OTP ──
            if ($request->filled('google_id')) {
                session([
                    'pending_registration' => [
                        'name'              => $request->name,
                        'email'             => $request->email,
                        'password'          => null,
                        'google_id'         => $request->google_id,
                        'otp_verified'      => true,
                        'registration_type' => 'user',
                    ],
                ]);

                session()->forget('google_user');

                Log::info('Google user registration — OTP skipped', ['email' => $request->email]);

                return redirect()->route('otp.verify.page')->with('toast', [
                    'type'    => 'success',
                    'message' => 'Google account verified. Completing your registration...',
                ]);
            }

            // ── Standard path — generate and send OTP ──
            $otp = random_int(100000, 999999);

            session([
                'pending_registration' => [
                    'name'              => $request->name,
                    'email'             => $request->email,
                    'password'          => $request->password,
                    'otp_code'          => $otp,
                    'otp_expires_at'    => now()->addMinutes(10)->toDateTimeString(),
                    'registration_type' => 'user',
                ],
            ]);

            Log::info('User registration data stored in session', ['email' => $request->email]);

            try {
                Mail::to($request->email)->send(new OtpVerificationMail($otp, $request->name));
                Log::info('OTP email sent to user', ['email' => $request->email]);
            } catch (\Exception $e) {
                Log::error('Failed to send OTP email to user', [
                    'error' => $e->getMessage(),
                    'email' => $request->email,
                ]);

                session()->forget('pending_registration');

                return back()->withInput()->withErrors([
                    'email' => 'Failed to send verification email. Please try again.',
                ]);
            }

            return redirect()->route('otp.verify.page')->with('toast', [
                'type'    => 'success',
                'message' => 'OTP has been sent to your email. Please verify your account.',
            ]);
        } catch (\Exception $e) {
            Log::error('User registration failed', [
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()->withErrors([
                'email' => 'Registration failed. Please try again.',
            ]);
        }
    }
}
