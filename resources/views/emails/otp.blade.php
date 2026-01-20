<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Your Account</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 20px;">

    <div style="max-width: 600px; margin: auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">

        <h2 style="text-align: center; color: #333;">Verify Your {{ $shopName }} Account</h2>

        <p>Hello,</p>

        <p>Thank you for registering your shop with {{ $shopName }}. To complete your registration, please use the OTP code below:</p>

        <h1 style="text-align: center; letter-spacing: 4px; font-size: 32px; color: #1d4ed8;">{{ $otp }}</h1>

        <p style="text-align: center; color: #555;">This code will expire in 10 minutes.</p>

        <p>Regards,<br>{{ $shopName }} Team</p>

    </div>
</body>
</html>
