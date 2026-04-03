<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Services\ShopService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Mail\OtpVerificationMail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class OtpVerificationController extends Controller
{
    public function __construct(
        private ShopService $shopService
    ) {}

    public function show()
    {
        if (!session('pending_registration')) {
            Log::warning('OTP verification page accessed without pending registration');
            return redirect()->route('register.shop')
                ->withErrors(['error' => 'No pending registration found. Please register again.']);
        }

        $pendingData = session('pending_registration');
        Log::info('OTP verification page accessed', ['email' => $pendingData['email']]);

        // Google users are already verified — skip OTP page entirely
        if (!empty($pendingData['otp_verified'])) {
            Log::info('Google user — skipping OTP', ['email' => $pendingData['email']]);
            return $this->createAccount($pendingData);
        }

        return Inertia::render('auth/VerifyOtp', [
            'email' => $pendingData['email'],
        ]);
    }

    public function verify(VerifyOtpRequest $request)
    {
        $pendingData = session('pending_registration');

        if (!$pendingData) {
            Log::warning('OTP verification attempted without pending registration');
            return redirect()->route('register.shop')
                ->withErrors(['error' => 'Session expired. Please register again.']);
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

        return $this->createAccount($pendingData);
    }

    public function resend()
    {
        $pendingData = session('pending_registration');

        if (!$pendingData) {
            Log::warning('OTP resend attempted without pending registration');
            return redirect()->route('register.shop')
                ->withErrors(['error' => 'Session expired. Please register again.']);
        }

        $otp = random_int(100000, 999999);

        $pendingData['otp_code']       = $otp;
        $pendingData['otp_expires_at'] = now()->addMinutes(10)->toDateTimeString();
        session(['pending_registration' => $pendingData]);

        Log::info('New OTP generated', ['email' => $pendingData['email']]);

        try {
            Mail::to($pendingData['email'])->send(new OtpVerificationMail($otp, $pendingData['name']));
            Log::info('OTP resent successfully', ['email' => $pendingData['email']]);

            return back()->with('toast', [
                'type'    => 'success',
                'message' => 'A new OTP has been sent to your email.',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to resend OTP email', [
                'error' => $e->getMessage(),
                'email' => $pendingData['email'],
            ]);

            return back()->withErrors([
                'error' => 'Failed to send OTP. Please try again later.',
            ]);
        }
    }

    // ── Shared account creation logic ─────────────────────────────────────────

    private function createAccount(array $pendingData)
    {
        try {
            DB::beginTransaction();

            // ── Guard: block duplicate email at creation time ──────────────────
            $existing = User::where('email', $pendingData['email'])->first();

            if ($existing) {
                DB::rollBack();
                session()->forget('pending_registration');

                // If Google user, just link the google_id and log them in
                if (!empty($pendingData['google_id']) && !$existing->google_id) {
                    $existing->update(['google_id' => $pendingData['google_id']]);
                }

                if (!empty($pendingData['google_id'])) {
                    Auth::login($existing);

                    Log::info('Google OAuth — email already exists, logged in instead', [
                        'email' => $existing->email,
                    ]);

                    return redirect()->intended(match ($existing->role) {
                        'super_admin' => route('admin.dashboard'),
                        'owner'       => $existing->orders()->where('status', 'approved')->exists()
                            ? route('shop.dashboard')
                            : route('plans'),
                        'staff'       => route('staff.dashboard'),
                        default       => route('landing'),
                    });
                }

                return redirect()->route('login')->with('toast', [
                    'type'    => 'error',
                    'message' => 'An account with this email already exists. Please log in.',
                ]);
            }

            // ── Create account ─────────────────────────────────────────────────
            $result = $this->shopService->registerShop([
                'name'         => $pendingData['name'],
                'email'        => $pendingData['email'],
                'password'     => $pendingData['password'],
                'shop_name'    => $pendingData['shop_name'],
                'phone'        => $pendingData['phone'],
                'branch_name'  => $pendingData['branch_name'] ?? null,
                'block_street' => $pendingData['block_street'],
                'municipality' => $pendingData['municipality'],
                'barangay'     => $pendingData['barangay'],
                'postal_code'  => $pendingData['postal_code'],
                'google_id'    => $pendingData['google_id'] ?? null,
            ]);

            $user = $result['owner'];

            $user->update([
                'email_verified_at' => now(),
                'is_verified'       => true,
            ]);

            DB::commit();

            session()->forget('pending_registration');

            Log::info('Account created successfully', [
                'email'      => $user->email,
                'via_google' => !empty($pendingData['google_id']),
            ]);

            return redirect()->route('login')->with('toast', [
                'type'    => 'success',
                'message' => 'Your account has been created. You can now login.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to create account', [
                'error' => $e->getMessage(),
                'email' => $pendingData['email'],
            ]);

            return back()->withErrors([
                'otp' => 'Failed to complete registration. Please try again.',
            ]);
        }
    }
}
