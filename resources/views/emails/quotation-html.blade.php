<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quote {{ $quotation->quote_number }}</title>
</head>
<body style="margin:0;padding:0;background:#eef1e8;font-family:Segoe UI,system-ui,sans-serif;color:#1a1f14;">
    <div style="max-width:640px;margin:0 auto;padding:24px 16px;">
        <div style="background:#fff;border-radius:12px;border:1px solid #c9d4b3;padding:24px;">
            <p style="margin:0 0 12px;">Hello {{ $quotation->customer_name }},</p>
            <p style="margin:0 0 16px;">Your repair quote <strong>{{ $quotation->quote_number }}</strong> from <strong>{{ config('neamee.company_name') }}</strong> is ready for review.</p>
            <p style="margin:0 0 8px;"><strong>Total due:</strong> ${{ number_format($quotation->total, 2) }}</p>
            <p style="margin:0 0 8px;"><strong>Expires:</strong> {{ $quotation->expires_at?->format('M j, Y') ?? '—' }}</p>
            <p style="margin:20px 0;text-align:center;">
                <a href="{{ $quoteUrl }}" style="display:inline-block;background:#556332;color:#fff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:600;">
                    Review &amp; e-sign quote
                </a>
            </p>
            <p style="margin:0;font-size:13px;color:#515c4a;">Open the link on your phone to review parts, labor, and sign electronically before work begins.</p>
        </div>
        <p style="text-align:center;font-size:12px;color:#515c4a;margin-top:16px;">
            {{ config('neamee.company_name') }} · {{ config('neamee.phone') }}
        </p>
    </div>
</body>
</html>
