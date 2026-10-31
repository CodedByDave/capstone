<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 48px 56px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10.5px; line-height: 1.6; color: #1c1917; }
        .brand { text-align: center; font-size: 9px; letter-spacing: 3px; color: #78716c; text-transform: uppercase; }
        h1 { text-align: center; font-size: 17px; margin: 8px 0 4px; }
        .meta { text-align: center; font-size: 9px; color: #78716c; padding-bottom: 14px; border-bottom: 2px solid #1c1917; }
        .parties { margin: 14px 0; padding: 8px 0; border-top: 1px solid #d6d3d1; border-bottom: 1px solid #d6d3d1; }
        h3 { font-size: 10.5px; margin: 14px 0 2px; }
        p { margin: 0 0 4px; text-align: justify; }
        .signatures { margin-top: 28px; border-top: 2px solid #1c1917; padding-top: 12px; page-break-inside: avoid; }
        .signatures .label { font-size: 9px; letter-spacing: 2px; color: #78716c; text-transform: uppercase; }
        table.sign { width: 100%; margin-top: 14px; border-collapse: collapse; }
        table.sign td { width: 50%; vertical-align: bottom; padding-right: 24px; }
        .line { height: 70px; border-bottom: 1px solid #1c1917; text-align: left; }
        .line img { max-height: 64px; max-width: 220px; }
        .line .pending { font-size: 9px; color: #a8a29e; line-height: 70px; }
        .name { font-weight: bold; margin-top: 4px; }
        .muted { color: #78716c; font-size: 9px; }
        .stamp { margin-top: 12px; padding-top: 8px; border-top: 1px solid #d6d3d1; font-size: 9px; color: #78716c; }
    </style>
</head>
<body>
    <div class="brand">LaundryHub</div>
    <h1>{{ $title }}</h1>
    <div class="meta">
        Agreement version {{ $version }} &middot; Effective {{ $effectiveAt }}
    </div>

    <div class="parties">
        <p><strong>Platform:</strong> LaundryHub</p>
        <p><strong>Business:</strong> {{ $businessName }}</p>
    </div>

    @foreach ($clauses as $clause)
        @if ($clause['heading'])
            <h3>{{ $clause['heading'] }}</h3>
        @endif
        <p>{!! nl2br(e($clause['body'])) !!}</p>
    @endforeach

    <div class="signatures">
        <div class="label">Signatures</div>
        <table class="sign">
            <tr>
                <td>
                    <div class="line">
                        @if ($ownerSignature)
                            <img src="{{ $ownerSignature }}" alt="Owner signature">
                        @else
                            <span class="pending">Owner signature pending</span>
                        @endif
                    </div>
                    <div class="name">{{ $signerName ?: '________________' }}</div>
                    <div class="muted">{{ $signerRole }}</div>
                    <div class="muted">For {{ $businessName }}</div>
                </td>
                <td>
                    <div class="line">
                        @if ($platformSignature)
                            <img src="{{ $platformSignature }}" alt="LaundryHub representative signature">
                        @else
                            <span class="pending">Awaiting platform signature</span>
                        @endif
                    </div>
                    @if ($platformSignerName)
                        <div class="name">{{ $platformSignerName }}</div>
                        <div class="muted">{{ $platformSignerRole }}</div>
                    @endif
                    <div class="muted">For LaundryHub</div>
                </td>
            </tr>
        </table>
        <div class="stamp">
            Business signed: {{ $signedAt ?? 'Not yet signed' }}
            @if ($platformSignedAt) &nbsp;&middot;&nbsp; Platform signed: {{ $platformSignedAt }} @endif
        </div>
    </div>
</body>
</html>
