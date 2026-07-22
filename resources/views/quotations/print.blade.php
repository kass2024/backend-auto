<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Car Repair Quote {{ $quotation->quote_number }}</title>
    <style>
        :root {
            --ink: #1a1f14;
            --muted: #5a6350;
            --line: #c5cbb8;
            --soft: #f3f5ee;
            --soft-2: #e8ecdf;
            --brand: #556332;
            --brand-dark: #434f29;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            color: var(--ink);
            background: #eef1e8;
            font-family: "Segoe UI", system-ui, -apple-system, sans-serif;
            font-size: 12.5px;
            line-height: 1.45;
        }
        .toolbar {
            max-width: 980px;
            margin: 0 auto;
            padding: 18px 16px 0;
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        .btn-print {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 0;
            border-radius: 8px;
            padding: 11px 20px;
            background: linear-gradient(135deg, #6d8a3c, var(--brand));
            color: #fff;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
        }
        .sheet {
            max-width: 980px;
            margin: 16px auto 40px;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(26, 31, 20, 0.08);
        }
        .company-bar {
            display: grid;
            grid-template-columns: 1.2fr 1.4fr 1fr 1fr;
            gap: 10px;
            padding: 14px 22px;
            border-bottom: 1px solid var(--line);
            font-size: 11px;
            color: var(--muted);
            align-items: center;
        }
        .company-bar strong { color: var(--ink); font-size: 13px; }
        .brand-inline {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .brand-inline img {
            width: 42px;
            height: 42px;
            object-fit: contain;
            border-radius: 50%;
        }
        .title-row {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            align-items: flex-start;
            justify-content: space-between;
            padding: 18px 22px 8px;
        }
        .title-row h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--ink);
        }
        .dates-box {
            border: 1px solid var(--ink);
            border-radius: 4px;
            padding: 8px 12px;
            min-width: 190px;
            font-size: 12px;
        }
        .quote-no {
            background: var(--soft-2);
            border-radius: 8px;
            padding: 10px 16px;
            font-size: 18px;
            font-weight: 700;
            color: var(--brand-dark);
            letter-spacing: 0.02em;
        }
        .status-pill {
            display: inline-block;
            margin-top: 8px;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .status-accepted { background: #dcfce7; color: #166534; }
        .status-open { background: #fef3c7; color: #b45309; }
        .body-grid {
            display: grid;
            grid-template-columns: 0.95fr 1.35fr;
            gap: 22px;
            padding: 10px 22px 18px;
        }
        .section-title {
            margin: 0 0 8px;
            padding-bottom: 4px;
            border-bottom: 1.5px solid var(--ink);
            font-size: 13px;
            font-weight: 700;
        }
        .left-col .block { margin-bottom: 16px; }
        .field {
            margin: 0 0 7px;
            font-size: 12.5px;
        }
        .field .label {
            display: block;
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--muted);
        }
        .underline {
            border-bottom: 1px solid var(--line);
            min-height: 18px;
            padding: 1px 0 2px;
        }
        .overview-box {
            border: 1px solid var(--line);
            border-radius: 4px;
            background: var(--soft);
            padding: 8px 10px;
            min-height: 56px;
            white-space: pre-wrap;
            font-size: 12px;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        table.items th {
            text-align: left;
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: var(--muted);
            border-bottom: 1.5px solid var(--ink);
            padding: 4px 4px 6px;
        }
        table.items th.num,
        table.items td.num {
            text-align: right;
            white-space: nowrap;
        }
        table.items td {
            padding: 6px 4px;
            border-bottom: 1px solid var(--line);
            vertical-align: top;
        }
        .totals {
            width: min(100%, 260px);
            margin-left: auto;
        }
        .totals .row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 12.5px;
        }
        .totals .due {
            margin-top: 8px;
            border: 1.5px solid var(--ink);
            border-radius: 4px;
            padding: 8px 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 700;
            font-size: 15px;
        }
        .footer {
            background: var(--soft);
            border-top: 1px solid var(--line);
            display: grid;
            grid-template-columns: 1.3fr 0.9fr;
            gap: 18px;
            padding: 16px 22px 20px;
        }
        .footer h3 {
            margin: 0 0 8px;
            padding-bottom: 4px;
            border-bottom: 1.5px solid var(--ink);
            font-size: 13px;
        }
        .footer p {
            margin: 0 0 8px;
            font-size: 11px;
            color: var(--muted);
        }
        .sign-area {
            margin-top: 10px;
            border: 1px dashed var(--line);
            border-radius: 6px;
            min-height: 90px;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
        }
        .sign-area img {
            max-width: 100%;
            max-height: 100px;
        }
        .sign-meta {
            margin-top: 8px;
            font-size: 11.5px;
        }
        .sign-meta .line {
            border-bottom: 1px solid var(--line);
            margin-top: 14px;
            padding-bottom: 2px;
        }
        @media (max-width: 800px) {
            .company-bar { grid-template-columns: 1fr; }
            .body-grid, .footer { grid-template-columns: 1fr; }
            .title-row h1 { font-size: 22px; }
        }
        @media print {
            body { background: #fff; }
            .toolbar { display: none !important; }
            .sheet {
                margin: 0;
                border: 0;
                border-radius: 0;
                box-shadow: none;
                max-width: none;
            }
            .overview-box { break-inside: avoid; }
            .footer { break-inside: avoid; }
        }
        @page { margin: 12mm; size: letter; }
    </style>
</head>
<body>
    <div class="toolbar">
        <button class="btn-print" type="button" onclick="window.print()">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Print / Save PDF
        </button>
    </div>

    <div class="sheet">
        <div class="company-bar">
            <div class="brand-inline">
                @if(!empty($logoUrl))
                    <img src="{{ $logoUrl }}" alt="">
                @endif
                <div>
                    <strong>{{ config('neamee.company_name') }}</strong><br>
                    Auto repair &amp; service
                </div>
            </div>
            <div>
                {{ config('neamee.address_line1') }}<br>
                {{ config('neamee.address_line2') }}
            </div>
            <div>
                {{ config('neamee.phone') }}
            </div>
            <div>
                Quote document<br>
                {{ $quotation->statusLabel() }}
            </div>
        </div>

        <div class="title-row">
            <div>
                <h1>Car Repair Quote</h1>
                @if($quotation->isAccepted())
                    <span class="status-pill status-accepted">Accepted · E-signed</span>
                @else
                    <span class="status-pill status-open">{{ $quotation->statusLabel() }}</span>
                @endif
            </div>
            <div class="dates-box">
                <div><strong>Date Issued:</strong> {{ $quotation->issued_at?->format('m/d/Y') ?? '—' }}</div>
                <div><strong>Expiration Date:</strong> {{ $quotation->expires_at?->format('m/d/Y') ?? '—' }}</div>
            </div>
            <div class="quote-no"># {{ $quotation->quote_number }}</div>
        </div>

        <div class="body-grid">
            <div class="left-col">
                <div class="block">
                    <h2 class="section-title">Customer Information</h2>
                    <div class="field"><span class="label">Customer Name</span><div class="underline">{{ $quotation->customer_name }}</div></div>
                    <div class="field"><span class="label">Customer Phone Number</span><div class="underline">{{ $quotation->customer_phone ?: '—' }}</div></div>
                    <div class="field"><span class="label">Customer Email Address</span><div class="underline">{{ $quotation->customer_email ?: '—' }}</div></div>
                </div>

                <div class="block">
                    <h2 class="section-title">Vehicle Information</h2>
                    <div class="field"><span class="label">Make</span><div class="underline">{{ $quotation->vehicle_make ?: '—' }}</div></div>
                    <div class="field"><span class="label">Model</span><div class="underline">{{ $quotation->vehicle_model ?: '—' }}</div></div>
                    <div class="field"><span class="label">Year</span><div class="underline">{{ $quotation->vehicle_year ?: '—' }}</div></div>
                    <div class="field"><span class="label">VIN</span><div class="underline">{{ $quotation->vehicle_vin ?: '—' }}</div></div>
                    <div class="field"><span class="label">Plate</span><div class="underline">{{ $quotation->vehicle_plate ?: '—' }}</div></div>
                </div>

                <div class="block">
                    <h2 class="section-title">Repair Overview</h2>
                    <div class="field"><span class="label">Problem Description</span></div>
                    <div class="overview-box">{{ $quotation->problem_description ?: '—' }}</div>
                    <div class="field" style="margin-top:10px;"><span class="label">Inspection Findings</span></div>
                    <div class="overview-box">{{ $quotation->inspection_findings ?: '—' }}</div>
                    <div class="field" style="margin-top:10px;"><span class="label">Proposed Repairs</span></div>
                    <div class="overview-box">{{ $quotation->proposed_repairs ?: '—' }}</div>
                </div>
            </div>

            <div class="right-col">
                <h2 class="section-title">Parts</h2>
                <table class="items">
                    <thead>
                        <tr>
                            <th>Part Description</th>
                            <th class="num">Quantity</th>
                            <th class="num">Unit Price ($)</th>
                            <th class="num">Total Cost ($)</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($quotation->partItems as $item)
                        <tr>
                            <td>{{ $item->description }}</td>
                            <td class="num">{{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }}</td>
                            <td class="num">{{ number_format((float) $item->unit_price, 2) }}</td>
                            <td class="num">{{ number_format((float) $item->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">No parts listed</td></tr>
                    @endforelse
                    </tbody>
                </table>

                <h2 class="section-title">Labor</h2>
                <table class="items">
                    <thead>
                        <tr>
                            <th>Labor Description</th>
                            <th class="num">Hours/Days</th>
                            <th class="num">Rate ($)</th>
                            <th class="num">Total Cost ($)</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($quotation->laborItems as $item)
                        <tr>
                            <td>{{ $item->description }}</td>
                            <td class="num">{{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }}</td>
                            <td class="num">{{ number_format((float) $item->unit_price, 2) }}</td>
                            <td class="num">{{ number_format((float) $item->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">No labor listed</td></tr>
                    @endforelse
                    </tbody>
                </table>

                <h2 class="section-title">Additional Costs</h2>
                <table class="items">
                    <thead>
                        <tr>
                            <th>Cost Description</th>
                            <th class="num">Quantity</th>
                            <th class="num">Unit Price ($)</th>
                            <th class="num">Total Cost ($)</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($quotation->additionalItems as $item)
                        <tr>
                            <td>{{ $item->description }}</td>
                            <td class="num">{{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }}</td>
                            <td class="num">{{ number_format((float) $item->unit_price, 2) }}</td>
                            <td class="num">{{ number_format((float) $item->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">No additional costs</td></tr>
                    @endforelse
                    </tbody>
                </table>

                <div class="totals">
                    <div class="row"><span>Subtotal</span><span>${{ number_format((float) $quotation->subtotal, 2) }}</span></div>
                    <div class="row"><span>Discount</span><span>${{ number_format((float) $quotation->discount, 2) }}</span></div>
                    <div class="row"><span>Tax Rate ({{ rtrim(rtrim(number_format((float) $quotation->tax_rate, 2), '0'), '.') }}%)</span><span>${{ number_format((float) $quotation->tax_amount, 2) }}</span></div>
                    <div class="due"><span>Total Due</span><span>${{ number_format((float) $quotation->total, 2) }}</span></div>
                </div>
            </div>
        </div>

        <div class="footer">
            <div>
                <h3>Terms &amp; Conditions</h3>
                <p><strong>Payment Terms.</strong> {{ $quotation->payment_terms }}</p>
                <p><strong>Warranty.</strong> {{ $quotation->warranty_terms }}</p>
                <p><strong>Authorization.</strong> {{ $quotation->authorization_terms }}</p>
                <p><strong>Cancellation Policy.</strong> Estimates may change if additional issues are discovered after work begins; the customer will be notified before extra charges are applied.</p>
                <p><strong>Liability.</strong> NEAMEE Auto-Tech is not liable for pre-existing conditions unrelated to the authorized repairs on this quote.</p>
            </div>
            <div>
                <h3>Acceptance</h3>
                <p>By signing below, the customer agrees to the terms outlined in this quote.</p>
                <div class="sign-area">
                    @if($quotation->signature_data)
                        <img src="{{ $quotation->signature_data }}" alt="Customer signature">
                    @else
                        <span style="color:#9aa392;">Signature</span>
                    @endif
                </div>
                <div class="sign-meta">
                    <div class="line"><strong>Customer Name:</strong> {{ $quotation->signature_name ?: $quotation->customer_name }}</div>
                    <div class="line" style="margin-top:10px;"><strong>Date:</strong> {{ $quotation->signed_at?->format('m/d/Y') ?? 'MM/DD/YYYY' }}</div>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($autoPrint))
        <script>window.addEventListener('load', function () { window.print(); });</script>
    @endif
</body>
</html>
