<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        $stateData = [];

        if (session()->has('checkout')) {
            $stateData['checkout'] = session('checkout');
        }

        $registerAs = request()->query('register_as');
        if ($registerAs) {
            $stateData['register_as'] = $registerAs;
        }

        $driver = Socialite::driver('google')->stateless();

        if (!empty($stateData)) {
            $driver->with(['state' => Crypt::encryptString(json_encode($stateData))]);
        }

        return $driver->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Exception $e) {
            Log::error('Google OAuth callback failed', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('login')->with('toast', [
                'type'    => 'error',
                'message' => 'Google login failed. Please try again.',
            ]);
        }

        // Restore state from encrypted param
        $rawState  = request()->input('state');
        $registerAs = null;
        if ($rawState) {
            try {
                $decoded = json_decode(Crypt::decryptString($rawState), true);
                if (!empty($decoded['checkout'])) {
                    session(['checkout' => $decoded['checkout']]);
                    Log::info('Checkout restored from OAuth state');
                }
                if (!empty($decoded['register_as'])) {
                    $registerAs = $decoded['register_as'];
                }
            } catch (\Exception $e) {
                Log::warning('Could not decrypt OAuth state', ['error' => $e->getMessage()]);
            }
        }

        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        // ── Existing user ──────────────────────────────────────────────────────
        if ($user) {
            if (!$user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }

            Auth::login($user);

            Log::info('Existing Google user logged in', ['email' => $user->email]);

            // Block checkout if already has active plan
            $hasActivePlan = $user->orders()
                ->whereIn('status', ['approved', 'paid', 'pending'])
                ->exists();

            if ($hasActivePlan) {
                session()->forget('checkout');

                return redirect()->route('shop.dashboard')->with('toast', [
                    'type'    => 'error',
                    'message' => 'You already have an active plan.',
                ]);
            }

            if (session()->has('checkout')) {
                return redirect()->route('checkout.confirm');
            }

            // Honor trial intent; discard everything else so OAuth login always
            // lands on the role's home page rather than a stale shop/admin URL.
            $intended = session()->pull('url.intended', '');

            if (str_contains($intended, '/trial')) {
                return redirect()->route('trial.show');
            }

            return redirect(match ($user->role) {
                'super_admin' => route('admin.dashboard'),
                'owner'       => route('shop.dashboard'),
                'staff'       => route('staff.dashboard'),
                'user'        => route('user.dashboard'),
                default       => route('landing'),
            });
        }

        // ── New user ───────────────────────────────────────────────────────────

        // Customer registration — skip the form entirely, create account directly
        if ($registerAs === 'user') {
            session([
                'pending_registration' => [
                    'name'              => $googleUser->getName(),
                    'email'             => $googleUser->getEmail(),
                    'password'          => null,
                    'google_id'         => $googleUser->getId(),
                    'otp_verified'      => true,
                    'registration_type' => 'user',
                ],
            ]);

            session()->save();

            Log::info('New Google user (customer) — skipping form, creating account', [
                'email' => $googleUser->getEmail(),
            ]);

            return redirect()->route('otp.verify.page')->with('toast', [
                'type'    => 'success',
                'message' => 'Google account verified. Creating your account...',
            ]);
        }

        // Shop registration — still needs shop details, go to register form
        session()->put('google_user', [
            'google_id' => $googleUser->getId(),
            'name'      => $googleUser->getName(),
            'email'     => $googleUser->getEmail(),
            'avatar'    => $googleUser->getAvatar(),
        ]);

        session()->save();

        Log::info('New Google user (shop) — redirecting to register', [
            'email' => $googleUser->getEmail(),
        ]);

        return redirect()->route('register.shop')->with('toast', [
            'type'    => 'info',
            'message' => 'Please complete your registration to continue.',
        ]);
    }
}
