<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 520px; margin: 40px auto; background: #fff; border-radius: 10px; padding: 32px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); }
        .header { font-size: 20px; font-weight: bold; color: #1a1a1a; margin-bottom: 8px; }
        .sub { color: #666; font-size: 14px; margin-bottom: 24px; }
        .button { display: inline-block; background: #1a1a1a; color: #fff !important; text-decoration: none; padding: 12px 22px; border-radius: 8px; font-size: 14px; font-weight: bold; }
        .note { font-size: 12px; color: #888; margin-top: 20px; }
        .footer { font-size: 12px; color: #aaa; margin-top: 24px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">Hello, {{ $name }}!</div>
        <div class="sub">
            {{ $shopName }} added you as a supplier. Set a password to open your supplier dashboard,
            where you can list the supplies you have in stock and upload your price list.
        </div>

        <a class="button" href="{{ $setPasswordUrl }}">Set your password</a>

        <div class="note">
            This link expires in {{ $expiresInMinutes }} minutes. If it expires, ask {{ $shopName }} to send a new invite,
            or use "Forgot password" on the login page.
        </div>

        <div class="footer">This is an automated message. Please do not reply to this email.</div>
    </div>
</body>
</html>
