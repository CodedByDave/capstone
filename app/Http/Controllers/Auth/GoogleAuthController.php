<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        // Encode checkout into the OAuth 'state' param so it survives stateless()
        $state = null;

        if (session()->has('checkout')) {
            $state = Crypt::encryptString(json_encode([
                'checkout' => session('checkout'),
            ]));
        }

        $driver = Socialite::driver('google')->stateless();

        if ($state) {
            $driver->with(['state' => $state]);
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

        // Restore checkout from encrypted state param
        $rawState = request()->input('state');
        if ($rawState) {
            try {
                $decoded = json_decode(Crypt::decryptString($rawState), true);
                if (!empty($decoded['checkout'])) {
                    session(['checkout' => $decoded['checkout']]);
                    Log::info('Checkout restored from OAuth state');
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

            if (session()->has('checkout')) {
                return redirect()->route('checkout.confirm');
            }

            return redirect()->intended(match ($user->role) {
                'super_admin' => route('admin.dashboard'),
                'owner'       => route('shop.dashboard'),
                'staff'       => route('staff.dashboard'),
                default       => route('landing'),
            });
        }

        // ── New user ───────────────────────────────────────────────────────────
        try {
            $user = User::create([
                'name'              => $googleUser->getName(),
                'email'             => $googleUser->getEmail(),
                'google_id'         => $googleUser->getId(),
                'password'          => bcrypt(Str::random(32)),
                'email_verified_at' => now(),
                'is_verified'       => true,
                'role'              => 'owner',
            ]);

            Auth::login($user);

            Log::info('New Google user registered', ['email' => $user->email]);

            if (session()->has('checkout')) {
                return redirect()->route('checkout.confirm');
            }

            return redirect()->route('landing')->with('toast', [
                'type'    => 'success',
                'message' => 'Welcome! Please select a plan to get started.',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create Google user', [
                'email'  => $googleUser->getEmail(),
                'error'  => $e->getMessage(),
            ]);

            return redirect()->route('login')->with('toast', [
                'type'    => 'error',
                'message' => 'Failed to create account. Please try again.',
            ]);
        }
    }
}
