<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pay Invoice {{ $invoice->invoice_number }}</title>
    <style>
        :root {
            --brand-primary: {{ config('neamee.brand.primary') }};
            --brand-primary-light: {{ config('neamee.brand.primary_light') }};
            --page-bg: {{ config('neamee.brand.page_bg') }};
            --text-muted: {{ config('neamee.brand.text_muted') }};
            --paid: {{ config('neamee.brand.paid') }};
        }
        body { font-family: 'Inter', Arial, sans-serif; background: var(--page-bg); margin:0; padding:24px; }
        .card { max-width:520px; margin:40px auto; background:#fff; border-radius:12px; border:1px solid {{ config('neamee.brand.border') }}; padding:28px; box-shadow:0 8px 24px rgba(26,31,20,.08); }
        h1 { margin:0 0 8px; font-size:22px; color:var(--brand-primary); }
        .amount { font-size:28px; font-weight:700; margin:16px 0; color: {{ config('neamee.brand.text') }}; }
        .btn { display:inline-block; background:linear-gradient(135deg, var(--brand-primary-light), var(--brand-primary)); color:#fff; text-decoration:none; padding:12px 20px; border-radius:8px; font-weight:700; }
        .muted { color:var(--text-muted); font-size:14px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Invoice {{ $invoice->invoice_number }}</h1>
        <p class="muted">Hello {{ $invoice->user?->name }},</p>
        <p class="muted">Total due</p>
        <div class="amount">${{ number_format($invoice->total, 2) }}</div>

        @if($invoice->isPaid())
            <p style="color:var(--paid);font-weight:700;">This invoice is already paid. Thank you!</p>
        @elseif($paymentUrl)
            <p><a class="btn" href="{{ $paymentUrl }}">Pay with Stripe</a></p>
            <p class="muted">Secure payment powered by Stripe. You can also pay in person at our shop.</p>
        @elseif($stripeConfigured)
            <p class="muted">Payment link is being prepared. Please refresh this page in a moment.</p>
        @else
            <p class="muted">Online card payment is not configured. Please pay at our shop or contact us for payment options.</p>
        @endif
    </div>
</body>
</html>
