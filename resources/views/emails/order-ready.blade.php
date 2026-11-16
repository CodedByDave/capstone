<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 520px; margin: 40px auto; background: #fff; border-radius: 10px; padding: 32px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); }
        .header { font-size: 20px; font-weight: bold; color: #1a1a1a; margin-bottom: 8px; }
        .sub { color: #444; font-size: 14px; line-height: 1.5; margin-bottom: 16px; }
        .order { background: #f4f4f4; border-radius: 8px; padding: 12px 16px; font-size: 14px; color: #1a1a1a; }
        .footer { font-size: 12px; color: #888; margin-top: 24px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">Hi {{ $name }}, your {{ $what }} is done!</div>
        <div class="sub">
            {{ $shopName }} finished your order.
            @if ($delivery)
                It will be delivered to you soon.
            @else
                It is ready for pickup at the shop.
            @endif
        </div>

        <div class="order">
            Order {{ $orderNumber }}
            @if ($balance > 0)
                <br>Balance due: &#8369;{{ number_format($balance, 2) }}
            @endif
        </div>

        <div class="footer">Thank you for choosing {{ $shopName }}. This is an automated message. Please do not reply to this email.</div>
    </div>
</body>
</html>
