<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        :root {
            --blue: #1e4f91;
            --blue-light: #e8f0f8;
            --blue-border: #b8cce0;
            --text: #1a1a1a;
            --text-muted: #4a5568;
            --paper: #ffffff;
            --page-bg: #eef2f6;
            --success: #166534;
            --warning: #b45309;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, BlinkMacSystemFont, Arial, sans-serif;
            font-size: 14px;
            line-height: 1.55;
            color: var(--text);
            background: var(--page-bg);
            min-height: 100vh;
            padding: 32px 20px 48px;
        }

        .toolbar {
            max-width: 920px;
            margin: 0 auto 20px;
            display: flex;
            justify-content: center;
        }

        .btn-print {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 22px;
            background: var(--blue);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(30, 79, 145, 0.25);
            transition: background 0.15s;
        }

        .btn-print:hover { background: #163d72; }

        .invoice-sheet {
            max-width: 920px;
            margin: 0 auto;
            background: var(--paper);
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08), 0 1px 3px rgba(0, 0, 0, 0.06);
            padding: 40px 44px 36px;
        }

        /* ── Header ── */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 32px;
            padding-bottom: 28px;
            border-bottom: 2px solid var(--blue);
            margin-bottom: 28px;
        }

        .brand {
            display: flex;
            gap: 16px;
            align-items: center;
        }

        .brand img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            border-radius: 50%;
            border: 2px solid var(--blue-border);
        }

        .brand-name {
            font-size: 17px;
            font-weight: 700;
            color: var(--blue);
            letter-spacing: 0.02em;
            margin-bottom: 6px;
        }

        .brand-details {
            font-size: 13px;
            color: var(--text-muted);
            line-height: 1.65;
        }

        .invoice-meta {
            text-align: right;
            min-width: 200px;
        }

        .invoice-meta h1 {
            font-size: 28px;
            font-weight: 800;
            color: var(--blue);
            letter-spacing: 0.06em;
            margin-bottom: 12px;
        }

        .meta-row {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 4px;
        }

        .meta-row strong {
            color: var(--text);
            font-weight: 600;
        }

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .badge-paid { background: #dcfce7; color: var(--success); }
        .badge-unpaid { background: #fef3c7; color: var(--warning); }

        /* ── Panels ── */
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .panel {
            border: 1px solid var(--blue-border);
            border-radius: 8px;
            overflow: hidden;
        }

        .panel-title {
            background: var(--blue);
            color: #fff;
            padding: 9px 14px;
            font-weight: 700;
            font-size: 12px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .panel-body { padding: 14px 16px; }

        .meta-table { width: 100%; border-collapse: collapse; }

        .meta-table tr + tr td { padding-top: 8px; }

        .meta-table td { vertical-align: top; font-size: 13px; }

        .meta-table .label {
            width: 42%;
            font-weight: 600;
            color: var(--blue);
            padding-right: 10px;
        }

        .meta-table .value { color: var(--text); }

        /* ── Line-item tables ── */
        table.items { width: 100%; border-collapse: collapse; font-size: 13px; }

        table.items th {
            background: var(--blue-light);
            color: var(--blue);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            padding: 9px 12px;
            border-bottom: 1px solid var(--blue-border);
            text-align: left;
        }

        table.items td {
            padding: 9px 12px;
            border-bottom: 1px solid #e8edf2;
            color: var(--text);
        }

        table.items tbody tr:last-child td { border-bottom: none; }

        table.items td.amount,
        table.items th.amount { text-align: right; white-space: nowrap; }

        .empty-row td {
            text-align: center;
            color: var(--text-muted);
            font-style: italic;
            padding: 18px 12px;
        }

        /* ── Work description ── */
        .work-box {
            min-height: 80px;
            background: #f8fafc;
            border: 1px solid var(--blue-border);
            border-radius: 6px;
            padding: 12px 14px;
            font-size: 13px;
            line-height: 1.6;
            white-space: pre-wrap;
            color: var(--text);
        }

        /* ── Totals ── */
        .totals-wrap {
            display: flex;
            justify-content: flex-end;
            margin-top: 24px;
            margin-bottom: 28px;
        }

        table.totals {
            width: 340px;
            border-collapse: collapse;
            font-size: 14px;
        }

        table.totals td {
            padding: 8px 14px;
            border: 1px solid var(--blue-border);
        }

        table.totals .label {
            font-weight: 600;
            color: var(--text-muted);
            background: #f8fafc;
            width: 65%;
        }

        table.totals .value {
            text-align: right;
            font-weight: 600;
            color: var(--text);
            width: 35%;
        }

        table.totals tr.grand td {
            background: var(--blue);
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            border-color: var(--blue);
            padding: 11px 14px;
        }

        table.totals tr.grand .label { background: var(--blue); color: #fff; }

        /* ── Footer ── */
        .footer {
            border-top: 1px solid var(--blue-border);
            padding-top: 20px;
            text-align: center;
        }

        .footer p {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 6px;
            line-height: 1.6;
        }

        .footer .thank-you {
            margin-top: 14px;
            font-size: 15px;
            font-weight: 600;
            color: var(--blue);
        }

        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none !important; }
            .invoice-sheet {
                box-shadow: none;
                border-radius: 0;
                padding: 24px;
                max-width: 100%;
            }
        }

        @media (max-width: 680px) {
            .invoice-sheet { padding: 24px 20px; }
            .header { flex-direction: column; }
            .invoice-meta { text-align: left; }
            .grid-2 { grid-template-columns: 1fr; }
            .totals-wrap { justify-content: stretch; }
            table.totals { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <button class="btn-print" onclick="window.print()">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Print / Save PDF
        </button>
    </div>

    <div class="invoice-sheet">
        <div class="header">
            <div class="brand">
                <img src="{{ asset(config('neamee.logo')) }}" alt="{{ config('neamee.company_name') }}">
                <div>
                    <div class="brand-name">{{ config('neamee.company_name') }}</div>
                    <div class="brand-details">
                        {{ config('neamee.address_line1') }}<br>
                        {{ config('neamee.address_line2') }}<br>
                        {{ config('neamee.phone') }}
                    </div>
                </div>
            </div>
            <div class="invoice-meta">
                <h1>INVOICE</h1>
                <div class="meta-row"><strong>#</strong> {{ $invoice->invoice_number }}</div>
                <div class="meta-row"><strong>Date:</strong> {{ $invoice->created_at->format('M j, Y') }}</div>
                <div class="meta-row"><strong>Due:</strong> {{ $invoice->due_date?->format('M j, Y') ?? 'Upon receipt' }}</div>
                <div class="meta-row" style="margin-top:8px;">
                    <span class="badge {{ $invoice->isPaid() ? 'badge-paid' : 'badge-unpaid' }}">
                        {{ $invoice->isPaid() ? 'Paid' : 'Unpaid' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="grid-2">
            <div class="panel">
                <div class="panel-title">Customer</div>
                <div class="panel-body">
                    <table class="meta-table">
                        <tr>
                            <td class="label">Name</td>
                            <td class="value">{{ $invoice->user?->name }}</td>
                        </tr>
                        <tr>
                            <td class="label">Phone</td>
                            <td class="value">{{ $invoice->user?->phone ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Address</td>
                            <td class="value">{{ $invoice->user?->address ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Email</td>
                            <td class="value">{{ $invoice->user?->email }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="panel">
                <div class="panel-title">Vehicle</div>
                <div class="panel-body">
                    @php $vehicle = $invoice->vehicle ?? $invoice->jobCard?->vehicle; @endphp
                    <table class="meta-table">
                        <tr>
                            <td class="label">Year / Make / Model</td>
                            <td class="value">{{ $vehicle ? trim("{$vehicle->year} {$vehicle->make} {$vehicle->model}") : '—' }}</td>
                        </tr>
                        <tr>
                            <td class="label">License / Plate</td>
                            <td class="value">{{ $vehicle?->plate_number ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Odometer</td>
                            <td class="value">{{ $invoice->odometer ? number_format($invoice->odometer).' mi' : '—' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Time received</td>
                            <td class="value">{{ $invoice->time_received?->format('M j, Y g:i A') ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Time promised</td>
                            <td class="value">{{ $invoice->time_promised?->format('M j, Y g:i A') ?? '—' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="grid-2">
            <div class="panel">
                <div class="panel-title">Parts used</div>
                <table class="items">
                    <thead>
                        <tr>
                            <th style="width:10%">Qty</th>
                            <th style="width:22%">Part no.</th>
                            <th>Description</th>
                            <th class="amount" style="width:18%">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($parts as $item)
                            <tr>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ $item->part_number ?? '—' }}</td>
                                <td>{{ $item->description }}</td>
                                <td class="amount">${{ number_format($item->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr class="empty-row"><td colspan="4">No parts listed</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="panel">
                <div class="panel-title">Services &amp; labor</div>
                <table class="items">
                    <thead>
                        <tr>
                            <th>Description of work</th>
                            <th style="width:10%">Qty</th>
                            <th class="amount" style="width:18%">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($services as $item)
                            <tr>
                                <td>{{ $item->description }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td class="amount">${{ number_format($item->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr class="empty-row"><td colspan="3">No services listed</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($invoice->work_description)
            <div class="panel" style="margin-bottom:0;">
                <div class="panel-title">Description of work</div>
                <div class="panel-body">
                    <div class="work-box">{{ $invoice->work_description }}</div>
                </div>
            </div>
        @endif

        <div class="totals-wrap">
            <table class="totals">
                <tr>
                    <td class="label">Total parts</td>
                    <td class="value">${{ number_format($invoice->parts_total, 2) }}</td>
                </tr>
                <tr>
                    <td class="label">Total labor / services</td>
                    <td class="value">${{ number_format($invoice->labor_total, 2) }}</td>
                </tr>
                @if($invoice->discount > 0)
                    <tr>
                        <td class="label">Discount</td>
                        <td class="value">-${{ number_format($invoice->discount, 2) }}</td>
                    </tr>
                @endif
                @if($invoice->tax_amount > 0)
                    <tr>
                        <td class="label">Tax ({{ $invoice->tax_rate }}%)</td>
                        <td class="value">${{ number_format($invoice->tax_amount, 2) }}</td>
                    </tr>
                @endif
                <tr class="grand">
                    <td class="label">Total amount</td>
                    <td class="value">${{ number_format($invoice->total, 2) }}</td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <p>Payment methods: Cash, Check, Bank Transfer, Credit Card (Stripe).</p>
            @if($invoice->isPaid())
                <p>Paid on {{ $invoice->paid_at?->format('M j, Y g:i A') }} via {{ str_replace('_', ' ', ucfirst($invoice->payment_method ?? 'payment')) }}.</p>
            @else
                <p>Please remit payment by the due date. Pay online using the secure Stripe link sent to your email.</p>
            @endif
            <p class="thank-you">Thank you for choosing {{ config('neamee.company_name') }}!</p>
        </div>
    </div>
</body>
</html>
