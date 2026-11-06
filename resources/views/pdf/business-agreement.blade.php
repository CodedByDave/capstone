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
        .acceptance { margin-top: 28px; border-top: 2px solid #1c1917; padding-top: 12px; page-break-inside: avoid; }
        .acceptance .label { font-size: 9px; letter-spacing: 2px; color: #78716c; text-transform: uppercase; }
        .acceptance p { margin-top: 6px; }
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

    {{-- The owner accepts by name; no signature is collected. --}}
    @if ($acceptedAt)
        <div class="acceptance">
            <div class="label">Acceptance</div>
            <p>Accepted electronically by <strong>{{ $signerName }}</strong> ({{ $signerRole }}) for {{ $businessName }} on {{ $acceptedAt }}.</p>
        </div>
    @endif
</body>
</html>
