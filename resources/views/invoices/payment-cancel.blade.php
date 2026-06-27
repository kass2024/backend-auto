<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment cancelled</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f4f6f8; margin:0; padding:24px; text-align:center; }
        .card { max-width:480px; margin:60px auto; background:#fff; border-radius:12px; padding:32px; box-shadow:0 8px 24px rgba(0,0,0,.08); }
        h1 { color:#b45309; }
        a { color:#1e4f91; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Payment cancelled</h1>
        <p>No charge was made for invoice <strong>{{ $invoice->invoice_number }}</strong>.</p>
        <p><a href="{{ route('invoice.pay', $invoice) }}">Try again</a></p>
    </div>
</body>
</html>
