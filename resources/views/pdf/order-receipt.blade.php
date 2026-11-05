<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $receipt['invoice_number'] }}</title>
    <style>
        @page { margin: 0; }
        body { margin: 0; padding: 22px 20px; font-family: DejaVu Sans, sans-serif; font-size: 9.5px; color: #111827; }
        .head { text-align: center; padding-bottom: 14px; border-bottom: 1px dashed #d1d5db; }
        .shop { font-size: 8.5px; letter-spacing: 2px; color: #6b7280; text-transform: uppercase; }
        .title { font-size: 15px; font-weight: bold; margin-top: 8px; }
        h4 { font-size: 10.5px; margin: 14px 0 6px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 4px 0; vertical-align: top; }
        td.k { color: #6b7280; width: 46%; }
        td.v { text-align: right; font-weight: bold; }
        .section { border-top: 1px dashed #d1d5db; margin-top: 10px; padding-top: 2px; }
        .badge { display: inline-block; padding: 2px 7px; border-radius: 3px; font-size: 8px; font-weight: bold; color: #fff; background: #16a34a; }
        .badge.partial { background: #d97706; }
        .badge.unpaid { background: #dc2626; }
        .note { margin-top: 12px; text-align: center; font-size: 8px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="head">
        <div class="shop">{{ $receipt['shop_name'] }}</div>
        <div class="title">{{ $receipt['title'] }}</div>
    </div>

    <h4>Payment Details</h4>
    <table>
        <tr><td class="k">Invoice Number</td><td class="v">{{ $receipt['invoice_number'] }}</td></tr>
        <tr><td class="k">Customer</td><td class="v">{{ $receipt['customer_name'] }}</td></tr>
        <tr><td class="k">Order Time</td><td class="v">{{ $receipt['order_time'] }}</td></tr>
        <tr><td class="k">Payment Method</td><td class="v">{{ $receipt['payment_method'] }}</td></tr>
        <tr><td class="k">Payment Status</td><td class="v"><span class="badge {{ $receipt['payment_status'] }}">{{ $receipt['payment_status_label'] }}</span></td></tr>
        <tr><td class="k">Amount</td><td class="v">&#8369;{{ number_format($receipt['amount_paid'], 2) }}</td></tr>
    </table>

    <div class="section">
        <h4>Order Details</h4>
        <table>
            @foreach ($receipt['items'] as $item)
                <tr>
                    <td class="k">{{ $item['label'] }}</td>
                    <td class="v">{{ $item['amount'] < 0 ? '-' : '' }}&#8369;{{ number_format(abs($item['amount']), 2) }}</td>
                </tr>
            @endforeach
            <tr><td class="k">Total Amount{{ $receipt['is_estimate'] ? ' (estimate)' : '' }}</td><td class="v">&#8369;{{ number_format($receipt['total'], 2) }}</td></tr>
            @if ($receipt['balance_due'] > 0)
                <tr><td class="k">Balance Due</td><td class="v">&#8369;{{ number_format($receipt['balance_due'], 2) }}</td></tr>
            @endif
            @if ($receipt['amount_tendered'] !== null)
                <tr><td class="k">Cash Received</td><td class="v">&#8369;{{ number_format($receipt['amount_tendered'], 2) }}</td></tr>
                <tr><td class="k">Change</td><td class="v">&#8369;{{ number_format($receipt['change_given'], 2) }}</td></tr>
            @endif
        </table>
    </div>

    <p class="note">Thank you for choosing {{ $receipt['shop_name'] }}.</p>
</body>
</html>
