<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Services\ShopService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Mail\OtpVerificationMail;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class OtpVerificationController extends Controller
{
    public function __construct(
        private ShopService $shopService
    ) {}

    public function show()
    {
        // Check if pending registration exists in session
        if (!session('pending_registration')) {
            Log::warning('OTP verification page accessed without pending registration');
            return redirect()->route('register.shop')->withErrors(['error' => 'No pending registration found. Please register again.']);
        }

        $pendingData = session('pending_registration');
        Log::info('OTP verification page accessed', ['email' => $pendingData['email']]);

        return Inertia::render('auth/VerifyOtp', [
            'email' => $pendingData['email']
        ]);
    }

    public function verify(VerifyOtpRequest $request)
    {
        $pendingData = session('pending_registration');

        if (!$pendingData) {
            Log::warning('OTP verification attempted without pending registration');
            return redirect()->route('register.shop')->withErrors(['error' => 'Session expired. Please register again.']);
        }

        // Check if OTP expired
        $expiresAt = Carbon::parse($pendingData['otp_expires_at']);
        if ($expiresAt->isPast()) {
            Log::warning('Expired OTP used', ['email' => $pendingData['email']]);
            return back()->withErrors(['otp' => 'OTP has expired. Please request a new one.']);
        }

        // Verify OTP
        if ($pendingData['otp_code'] != $request->otp) {
            Log::warning('Invalid OTP entered', ['email' => $pendingData['email']]);
            return back()->withErrors(['otp' => 'OTP is invalid.']);
        }

        // OTP is valid - NOW create the user in database
        try {
            DB::beginTransaction();

            $result = $this->shopService->registerShop([
                'name'         => $pendingData['name'],
                'email'        => $pendingData['email'],
                'password'     => $pendingData['password'],
                'shop_name'    => $pendingData['shop_name'],
                'phone'        => $pendingData['phone'],
                'block_street' => $pendingData['block_street'],
                'municipality' => $pendingData['municipality'],
                'barangay'     => $pendingData['barangay'],
                'postal_code'  => $pendingData['postal_code'],
            ]);
            $user = $result['owner'];

            // Mark email verified since OTP confirmed it
            $user->update(['email_verified_at' => now()]);

            DB::commit();

            session()->forget('pending_registration');

            return redirect()->route('login.user')->with('toast', [
                'type'    => 'success',
                'message' => 'Your account has been verified. You can now login.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to create user after OTP verification', [
                'error' => $e->getMessage(),
                'email' => $pendingData['email']
            ]);

            return back()->withErrors([
                'otp' => 'Failed to complete registration. Please try again.'
            ]);
        }
    }

    public function resend()
    {
        $pendingData = session('pending_registration');

        if (!$pendingData) {
            Log::warning('OTP resend attempted without pending registration');
            return redirect()->route('register.shop')->withErrors(['error' => 'Session expired. Please register again.']);
        }

        // Generate new OTP
        $otp = random_int(100000, 999999);

        // Update session with new OTP
        $pendingData['otp_code'] = $otp;
        $pendingData['otp_expires_at'] = now()->addMinutes(10)->toDateTimeString();
        session(['pending_registration' => $pendingData]);

        Log::info('New OTP generated', ['email' => $pendingData['email']]);

        // Send OTP email
        try {
            $shopName = $pendingData['name']; // Use user's name instead of shop name
            Mail::to($pendingData['email'])->send(new OtpVerificationMail($otp, $shopName));

            Log::info('OTP resent successfully', ['email' => $pendingData['email']]);

            return back()->with('toast', [
                'type' => 'success',
                'message' => 'A new OTP has been sent to your email.'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to resend OTP email', [
                'error' => $e->getMessage(),
                'email' => $pendingData['email']
            ]);

            return back()->withErrors([
                'error' => 'Failed to send OTP. Please try again later.'
            ]);
        }
    }
}
