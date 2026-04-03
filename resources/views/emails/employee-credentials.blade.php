<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 520px; margin: 40px auto; background: #fff; border-radius: 10px; padding: 32px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); }
        .header { font-size: 20px; font-weight: bold; color: #1a1a1a; margin-bottom: 8px; }
        .sub { color: #666; font-size: 14px; margin-bottom: 24px; }
        .credentials { background: #f8f8f8; border-radius: 8px; padding: 20px; margin-bottom: 24px; }
        .label { font-size: 12px; color: #888; margin-bottom: 2px; }
        .value { font-size: 15px; font-weight: bold; color: #1a1a1a; margin-bottom: 14px; }
        .warning { font-size: 12px; color: #e53e3e; margin-top: 16px; }
        .footer { font-size: 12px; color: #aaa; margin-top: 24px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">Welcome to {{ $shopName }}, {{ $name }}!</div>
        <div class="sub">Your employee account has been created. Here are your login credentials:</div>

        <div class="credentials">
            <div class="label">Email</div>
            <div class="value">{{ $email }}</div>

            <div class="label">Password</div>
            <div class="value">{{ $password }}</div>
        </div>

        <p style="font-size:14px; color:#444;">
            Please log in and change your password immediately after your first login.
        </p>

        <div class="warning">⚠ Do not share your credentials with anyone.</div>

        <div class="footer">This is an automated message from {{ $shopName }}. Please do not reply to this email.</div>
    </div>
</body>
</html>
