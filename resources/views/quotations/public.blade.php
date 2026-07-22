<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Quote {{ $quotation->quote_number }} · {{ config('neamee.company_name') }}</title>
    <style>
        :root {
            --primary: {{ config('neamee.brand.primary') }};
            --primary-dark: {{ config('neamee.brand.primary_dark') }};
            --surface: {{ config('neamee.brand.surface') }};
            --border: {{ config('neamee.brand.border') }};
            --text: {{ config('neamee.brand.text') }};
            --muted: {{ config('neamee.brand.text_muted') }};
            --page: {{ config('neamee.brand.page_bg') }};
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--page);
            color: var(--text);
            line-height: 1.5;
            -webkit-text-size-adjust: 100%;
        }
        .wrap {
            max-width: 920px;
            margin: 0 auto;
            padding: 16px 14px 40px;
        }
        .card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 18px 16px;
            margin-bottom: 14px;
            box-shadow: 0 2px 12px rgba(26, 31, 20, 0.06);
        }
        .header {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            justify-content: space-between;
            align-items: flex-start;
        }
        .brand {
            display: flex;
            gap: 12px;
            align-items: center;
        }
        .brand img {
            width: 52px;
            height: 52px;
            object-fit: contain;
            border-radius: 50%;
        }
        .brand h1 {
            margin: 0;
            font-size: 1.25rem;
            color: var(--primary-dark);
        }
        .brand p { margin: 2px 0 0; font-size: 0.85rem; color: var(--muted); }
        .meta {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 10px 12px;
            min-width: min(100%, 220px);
            font-size: 0.9rem;
        }
        .quote-no {
            background: var(--primary);
            color: #fff;
            border-radius: 10px;
            padding: 10px 14px;
            font-weight: 700;
            letter-spacing: 0.02em;
        }
        .badge {
            display: inline-block;
            margin-top: 8px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-ok { background: #dcfce7; color: #166534; }
        .badge-wait { background: #fef3c7; color: #b45309; }
        h2 {
            margin: 0 0 10px;
            font-size: 1rem;
            color: var(--primary-dark);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .grid {
            display: grid;
            gap: 12px;
        }
        @media (min-width: 720px) {
            .grid-2 { grid-template-columns: 1fr 1fr; }
            .wrap { padding: 24px 20px 48px; }
            .card { padding: 22px 24px; }
        }
        .field { margin-bottom: 8px; font-size: 0.95rem; }
        .field span { display: block; font-size: 0.75rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.04em; }
        .block-text {
            white-space: pre-wrap;
            background: var(--surface);
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 0.92rem;
            margin: 0 0 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }
        th, td {
            padding: 8px 6px;
            border-bottom: 1px solid var(--border);
            text-align: left;
            vertical-align: top;
        }
        th {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--muted);
        }
        td.num, th.num { text-align: right; white-space: nowrap; }
        .totals {
            margin-top: 12px;
            margin-left: auto;
            width: min(100%, 280px);
        }
        .totals .row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 0.95rem;
        }
        .totals .grand {
            margin-top: 8px;
            padding: 10px 12px;
            background: var(--primary-dark);
            color: #fff;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1.05rem;
        }
        .terms p {
            font-size: 0.82rem;
            color: var(--muted);
            margin: 0 0 10px;
        }
        .alert {
            background: #dcfce7;
            border: 1px solid #86efac;
            color: #166534;
            padding: 12px 14px;
            border-radius: 10px;
            margin-bottom: 14px;
            font-size: 0.95rem;
        }
        .alert-error {
            background: #fef2f2;
            border-color: #fecaca;
            color: #991b1b;
        }
        .sign-box {
            border: 1px dashed var(--border);
            border-radius: 12px;
            padding: 14px;
            background: var(--surface);
        }
        .sign-box label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 6px;
        }
        .sign-box input[type=text] {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 1rem;
            margin-bottom: 12px;
        }
        #signature-pad {
            width: 100%;
            height: 180px;
            touch-action: none;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 8px;
            display: block;
        }
        .sign-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 12px;
        }
        .btn {
            appearance: none;
            border: 0;
            border-radius: 10px;
            padding: 12px 18px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            flex: 1 1 140px;
            text-align: center;
        }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-secondary { background: #fff; color: var(--primary-dark); border: 1px solid var(--border); }
        .signed img {
            max-width: 100%;
            height: auto;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: #fff;
        }
        .sticky-cta {
            position: sticky;
            bottom: 0;
            background: linear-gradient(transparent, var(--page) 30%);
            padding: 16px 0 8px;
            margin-top: 8px;
        }
        .top-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 12px;
        }
        .top-actions a {
            text-decoration: none;
        }
        @media print {
            .sign-actions, .sticky-cta, .no-print { display: none !important; }
            body { background: #fff; }
            .card { box-shadow: none; }
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="top-actions no-print">
        <a class="btn btn-secondary" href="{{ route('quotation.print', ['quotation' => $quotation->id, 'token' => $token]) }}" target="_blank" rel="noopener">
            Print / Save PDF
        </a>
    </div>

    @if(session('status'))
        <div class="alert">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif

    <div class="card header">
        <div class="brand">
            @if(!empty($logoUrl))
                <img src="{{ $logoUrl }}" alt="{{ config('neamee.company_name') }}">
            @endif
            <div>
                <h1>Car Repair Quote</h1>
                <p>{{ config('neamee.company_name') }}<br>{{ config('neamee.phone') }}</p>
            </div>
        </div>
        <div>
            <div class="quote-no"># {{ $quotation->quote_number }}</div>
            @if($quotation->isAccepted())
                <span class="badge badge-ok">Accepted</span>
            @elseif($quotation->isExpired())
                <span class="badge badge-wait">Expired</span>
            @else
                <span class="badge badge-wait">Awaiting e-sign</span>
            @endif
        </div>
        <div class="meta">
            <div><strong>Date issued:</strong> {{ $quotation->issued_at?->format('m/d/Y') ?? '—' }}</div>
            <div><strong>Expiration:</strong> {{ $quotation->expires_at?->format('m/d/Y') ?? '—' }}</div>
        </div>
    </div>

    <div class="grid grid-2">
        <div class="card">
            <h2>Customer information</h2>
            <div class="field"><span>Name</span>{{ $quotation->customer_name }}</div>
            <div class="field"><span>Phone</span>{{ $quotation->customer_phone ?: '—' }}</div>
            <div class="field"><span>Email</span>{{ $quotation->customer_email ?: '—' }}</div>

            <h2 style="margin-top:18px;">Vehicle information</h2>
            <div class="field"><span>Make</span>{{ $quotation->vehicle_make ?: '—' }}</div>
            <div class="field"><span>Model</span>{{ $quotation->vehicle_model ?: '—' }}</div>
            <div class="field"><span>Year</span>{{ $quotation->vehicle_year ?: '—' }}</div>
            <div class="field"><span>VIN</span>{{ $quotation->vehicle_vin ?: '—' }}</div>
            <div class="field"><span>Plate</span>{{ $quotation->vehicle_plate ?: '—' }}</div>

            <h2 style="margin-top:18px;">Repair overview</h2>
            <div class="field"><span>Problem description</span></div>
            <p class="block-text">{{ $quotation->problem_description ?: '—' }}</p>
            <div class="field"><span>Inspection findings</span></div>
            <p class="block-text">{{ $quotation->inspection_findings ?: '—' }}</p>
            <div class="field"><span>Proposed repairs</span></div>
            <p class="block-text">{{ $quotation->proposed_repairs ?: '—' }}</p>
        </div>

        <div class="card">
            <h2>Parts</h2>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                    <tr>
                        <th>Part description</th>
                        <th class="num">Qty</th>
                        <th class="num">Unit</th>
                        <th class="num">Total</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($quotation->partItems as $item)
                        <tr>
                            <td>{{ $item->description }}</td>
                            <td class="num">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                            <td class="num">${{ number_format($item->unit_price, 2) }}</td>
                            <td class="num">${{ number_format($item->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">No parts listed</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <h2 style="margin-top:18px;">Labor</h2>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                    <tr>
                        <th>Labor description</th>
                        <th class="num">Hrs/Qty</th>
                        <th class="num">Rate</th>
                        <th class="num">Total</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($quotation->laborItems as $item)
                        <tr>
                            <td>{{ $item->description }}</td>
                            <td class="num">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                            <td class="num">${{ number_format($item->unit_price, 2) }}</td>
                            <td class="num">${{ number_format($item->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">No labor listed</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <h2 style="margin-top:18px;">Additional costs</h2>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                    <tr>
                        <th>Cost description</th>
                        <th class="num">Qty</th>
                        <th class="num">Unit</th>
                        <th class="num">Total</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($quotation->additionalItems as $item)
                        <tr>
                            <td>{{ $item->description }}</td>
                            <td class="num">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                            <td class="num">${{ number_format($item->unit_price, 2) }}</td>
                            <td class="num">${{ number_format($item->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">No additional costs</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="totals">
                <div class="row"><span>Subtotal</span><span>${{ number_format($quotation->subtotal, 2) }}</span></div>
                @if($quotation->discount > 0)
                    <div class="row"><span>Discount</span><span>-${{ number_format($quotation->discount, 2) }}</span></div>
                @endif
                @if($quotation->tax_amount > 0)
                    <div class="row"><span>Tax ({{ $quotation->tax_rate }}%)</span><span>${{ number_format($quotation->tax_amount, 2) }}</span></div>
                @endif
                <div class="row grand"><span>Total due</span><span>${{ number_format($quotation->total, 2) }}</span></div>
            </div>
        </div>
    </div>

    <div class="card terms">
        <h2>Terms &amp; conditions</h2>
        <p><strong>Payment terms.</strong> {{ $quotation->payment_terms }}</p>
        <p><strong>Warranty.</strong> {{ $quotation->warranty_terms }}</p>
        <p><strong>Authorization.</strong> {{ $quotation->authorization_terms }}</p>
    </div>

    <div class="card" id="acceptance">
        <h2>Acceptance</h2>
        <p style="margin-top:0;color:var(--muted);font-size:0.92rem;">
            By signing below, the customer agrees to the terms outlined in this quote. Work and invoicing begin after e-signature.
        </p>

        @if($quotation->isAccepted())
            <div class="signed">
                <div class="field"><span>Signed by</span>{{ $quotation->signature_name }}</div>
                <div class="field"><span>Signed at</span>{{ $quotation->signed_at?->format('M j, Y g:i A') }}</div>
                @if($quotation->signature_data)
                    <img src="{{ $quotation->signature_data }}" alt="Customer signature">
                @endif
            </div>
        @elseif($quotation->canBeSigned())
            <form method="post" action="{{ route('quotation.sign', ['quotation' => $quotation->id, 'token' => $token]) }}" id="sign-form" class="sign-box">
                @csrf
                <label for="signature_name">Full name</label>
                <input type="text" id="signature_name" name="signature_name" required maxlength="255" placeholder="Type your full name" value="{{ old('signature_name', $quotation->customer_name) }}">

                <label>Draw your signature</label>
                <canvas id="signature-pad" width="600" height="180"></canvas>
                <input type="hidden" name="signature_data" id="signature_data">

                <div class="sign-actions">
                    <button type="button" class="btn btn-secondary" id="clear-sign">Clear</button>
                    <button type="submit" class="btn btn-primary">Accept &amp; e-sign</button>
                </div>
            </form>
        @else
            <p style="color:var(--muted);">This quote is no longer open for signature.</p>
        @endif
    </div>

    @if($quotation->canBeSigned())
        <div class="sticky-cta no-print">
            <a class="btn btn-primary" href="#acceptance" style="display:block;text-decoration:none;">Jump to e-sign</a>
        </div>
    @endif
</div>

@if($quotation->canBeSigned())
<script>
(function () {
    const canvas = document.getElementById('signature-pad');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    let drawing = false;
    let hasInk = false;

    function resize() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        const rect = canvas.getBoundingClientRect();
        const data = hasInk ? canvas.toDataURL() : null;
        canvas.width = Math.floor(rect.width * ratio);
        canvas.height = Math.floor(180 * ratio);
        ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
        ctx.lineWidth = 2.2;
        ctx.lineCap = 'round';
        ctx.strokeStyle = '#1a1f14';
        if (data) {
            const img = new Image();
            img.onload = function () { ctx.drawImage(img, 0, 0, rect.width, 180); };
            img.src = data;
        }
    }

    function pos(e) {
        const rect = canvas.getBoundingClientRect();
        const t = e.touches ? e.touches[0] : e;
        return { x: t.clientX - rect.left, y: t.clientY - rect.top };
    }

    function start(e) {
        e.preventDefault();
        drawing = true;
        const p = pos(e);
        ctx.beginPath();
        ctx.moveTo(p.x, p.y);
    }
    function move(e) {
        if (!drawing) return;
        e.preventDefault();
        const p = pos(e);
        ctx.lineTo(p.x, p.y);
        ctx.stroke();
        hasInk = true;
    }
    function end(e) {
        if (!drawing) return;
        e.preventDefault();
        drawing = false;
    }

    canvas.addEventListener('mousedown', start);
    canvas.addEventListener('mousemove', move);
    window.addEventListener('mouseup', end);
    canvas.addEventListener('touchstart', start, { passive: false });
    canvas.addEventListener('touchmove', move, { passive: false });
    canvas.addEventListener('touchend', end);

    document.getElementById('clear-sign').addEventListener('click', function () {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        hasInk = false;
    });

    document.getElementById('sign-form').addEventListener('submit', function (e) {
        if (!hasInk) {
            e.preventDefault();
            alert('Please draw your signature before accepting.');
            return;
        }
        document.getElementById('signature_data').value = canvas.toDataURL('image/png');
    });

    window.addEventListener('resize', resize);
    resize();
})();
</script>
@endif
</body>
</html>
