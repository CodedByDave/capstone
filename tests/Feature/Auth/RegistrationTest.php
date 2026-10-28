<?php

use App\Mail\OtpVerificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

test('registration screen can be rendered', function () {
    $response = $this->get(route('register.user'));

    $response->assertStatus(200);
});

test('new users can register through a shop QR code', function () {
    Mail::fake();
    $shop = referralShop();
    $this->withSession(['referral_shop_id' => $shop->id]);

    $response = $this->post(route('register.user.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertGuest();
    $response->assertRedirect(route('otp.verify.page', absolute: false));
    $response->assertSessionHas('pending_registration.email', 'test@example.com');
    Mail::assertSent(OtpVerificationMail::class);
});

test('rate limited users receive a registration notification', function () {
    $this->withSession(['referral_shop_id' => referralShop()->id]);
    RateLimiter::increment(sha1('|127.0.0.1'), amount: 5);

    $response = $this->from(route('register.user'))->post(route('register.user.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response
        ->assertRedirect(route('register.user'))
        ->assertSessionHas('toast.type', 'error')
        ->assertSessionHas('toast.message', fn (string $message) => str_starts_with(
            $message,
            'Too many attempts. Please try again in ',
        ));
});
