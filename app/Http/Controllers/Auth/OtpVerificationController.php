<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerifyOtpRequest;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpVerificationMail;
use Inertia\Inertia;

class OtpVerificationController extends Controller
{
    public function show()
    {
        return Inertia::render('auth/VerifyOtp');
    }

    public function verify(VerifyOtpRequest $request)
    {
        $user = auth()->user();

        if (!$user->otp_code || !$user->otp_expires_at) {
            return back()->withErrors(['otp' => 'OTP not found.']);
        }

        if ($user->otp_expires_at->isPast()) {
            return back()->withErrors(['otp' => 'OTP has expired.']);
        }

        if ($user->otp_code != $request->otp) {
            return back()->withErrors(['otp' => 'OTP is invalid.']);
        }

        // Mark email verified
        $user->update([
            'email_verified_at' => now(),
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);

        // Logout the user before redirecting to login
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('toast', [
            'type' => 'success',
            'message' => 'Your account has been verified. You can now login.'
        ]);
    }

    public function resend()
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $otp = random_int(100000, 999999);
        $user->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($user->email)->send(new OtpVerificationMail($otp, $user->shop->shop_name));

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'A new OTP has been sent to your email.'
        ]);
    }
}
