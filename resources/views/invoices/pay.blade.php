<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pay Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f4f6f8; margin:0; padding:24px; }
        .card { max-width:520px; margin:40px auto; background:#fff; border-radius:12px; padding:28px; box-shadow:0 8px 24px rgba(0,0,0,.08); }
        h1 { margin:0 0 8px; font-size:22px; color:#1e4f91; }
        .amount { font-size:28px; font-weight:700; margin:16px 0; }
        .btn { display:inline-block; background:#556332; color:#fff; text-decoration:none; padding:12px 20px; border-radius:8px; font-weight:700; }
        .muted { color:#666; font-size:14px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Invoice {{ $invoice->invoice_number }}</h1>
        <p class="muted">Hello {{ $invoice->user?->name }},</p>
        <p class="muted">Total due</p>
        <div class="amount">${{ number_format($invoice->total, 2) }}</div>

        @if($invoice->isPaid())
            <p style="color:#166534;font-weight:700;">This invoice is already paid. Thank you!</p>
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
