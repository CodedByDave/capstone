<?php

use App\Mail\OtpVerificationMail;
use Illuminate\Support\Facades\Mail;

test('registration screen can be rendered', function () {
    $response = $this->get(route('register.user'));

    $response->assertStatus(200);
});

test('new users can register', function () {
    Mail::fake();

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
