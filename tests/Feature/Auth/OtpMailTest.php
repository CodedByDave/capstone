<?php

use App\Mail\OtpVerificationMail;

test('the verification email shows the code in both the HTML and plain-text versions', function () {
    $mail = new OtpVerificationMail('482915', 'Sample Laundry');

    $mail->assertSeeInHtml('482915');
    $mail->assertSeeInText('482915');
    $mail->assertSeeInText('expire in 10 minutes');
});

test('the code is also in the subject line', function () {
    $mail = new OtpVerificationMail('482915', 'Sample Laundry');

    $mail->assertHasSubject('482915 is your '.config('app.name').' verification code');
});
