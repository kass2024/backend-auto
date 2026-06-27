<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment received</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f4f6f8; margin:0; padding:24px; text-align:center; }
        .card { max-width:480px; margin:60px auto; background:#fff; border-radius:12px; padding:32px; box-shadow:0 8px 24px rgba(0,0,0,.08); }
        h1 { color:#166534; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Payment received</h1>
        <p>Thank you, {{ $invoice->user?->name }}. Invoice <strong>{{ $invoice->invoice_number }}</strong> is now marked as <strong>paid</strong>.</p>
        <p>Total: ${{ number_format($invoice->total, 2) }}</p>
    </div>
</body>
</html>
