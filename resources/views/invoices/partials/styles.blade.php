@php
    $b = config('neamee.brand');
@endphp

* { box-sizing: border-box; margin: 0; padding: 0; }

body.invoice-document-body {
    font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
    font-size: 14px;
    line-height: 1.55;
    color: {{ $b['text'] }};
    background: {{ $b['page_bg'] }};
}

.invoice-toolbar {
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
    background: linear-gradient(135deg, {{ $b['primary_light'] }} 0%, {{ $b['primary'] }} 100%);
    color: #fff;
    border: 1px solid {{ $b['primary_dark'] }};
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 2px 10px rgba(85, 99, 50, 0.28);
}

.invoice-sheet {
    max-width: 920px;
    margin: 0 auto;
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid {{ $b['border'] }};
    box-shadow: 0 4px 24px rgba(26, 31, 20, 0.08), 0 1px 3px rgba(26, 31, 20, 0.05);
    padding: 40px 44px 36px;
}

.invoice-email-intro {
    max-width: 920px;
    margin: 0 auto 16px;
    padding: 0 4px;
    font-size: 14px;
    color: {{ $b['text_muted'] }};
    line-height: 1.6;
}

.invoice-email-intro p { margin-bottom: 8px; }

.invoice-stripe-cta {
    display: inline-block;
    margin: 16px 0 0;
    padding: 12px 22px;
    background: linear-gradient(135deg, {{ $b['primary_light'] }} 0%, {{ $b['primary'] }} 100%);
    color: #fff !important;
    text-decoration: none !important;
    border-radius: 8px;
    font-weight: 700;
    font-size: 14px;
}

.invoice-pdf-cta {
    display: inline-block;
    margin: 12px 8px 0;
    padding: 12px 22px;
    background: #ffffff;
    color: {{ $b['primary_dark'] }} !important;
    text-decoration: none !important;
    border-radius: 8px;
    font-weight: 700;
    font-size: 14px;
    border: 2px solid {{ $b['primary'] }};
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 32px;
    padding-bottom: 28px;
    border-bottom: 2px solid {{ $b['primary'] }};
    margin-bottom: 28px;
}

.brand { display: flex; gap: 16px; align-items: center; }

.brand img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 50%;
    border: 2px solid rgba(109, 138, 60, 0.55);
    box-shadow: 0 0 0 3px {{ $b['surface_alt'] }};
}

.brand-name {
    font-family: 'Rajdhani', 'Inter', sans-serif;
    font-size: 18px;
    font-weight: 700;
    color: {{ $b['primary_dark'] }};
    letter-spacing: 0.03em;
    margin-bottom: 6px;
    text-transform: uppercase;
}

.brand-details { font-size: 13px; color: {{ $b['text_muted'] }}; line-height: 1.65; }

.invoice-meta { text-align: right; min-width: 200px; }

.invoice-meta h1 {
    font-family: 'Rajdhani', 'Inter', sans-serif;
    font-size: 30px;
    font-weight: 700;
    color: {{ $b['primary'] }};
    letter-spacing: 0.1em;
    margin-bottom: 12px;
}

.meta-row { font-size: 13px; color: {{ $b['text_muted'] }}; margin-bottom: 4px; }
.meta-row strong { color: {{ $b['text'] }}; font-weight: 600; }

.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}

.badge-paid { background: {{ $b['paid_bg'] }}; color: {{ $b['paid'] }}; border: 1px solid rgba(22, 101, 52, 0.25); }
.badge-unpaid { background: {{ $b['unpaid_bg'] }}; color: {{ $b['unpaid'] }}; border: 1px solid rgba(180, 83, 9, 0.25); }

.grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.panel {
    border: 1px solid {{ $b['border'] }};
    border-radius: 8px;
    overflow: hidden;
    background: #ffffff;
}

.panel-title {
    background: linear-gradient(135deg, {{ $b['primary_light'] }} 0%, {{ $b['primary'] }} 100%);
    color: #fff;
    padding: 9px 14px;
    font-family: 'Rajdhani', 'Inter', sans-serif;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.panel-body { padding: 14px 16px; }

.meta-table { width: 100%; border-collapse: collapse; }
.meta-table tr + tr td { padding-top: 8px; }
.meta-table td { vertical-align: top; font-size: 13px; }
.meta-table .label { width: 42%; font-weight: 600; color: {{ $b['primary'] }}; padding-right: 10px; }
.meta-table .value { color: {{ $b['text'] }}; }

table.items { width: 100%; border-collapse: collapse; font-size: 13px; }

table.items th {
    background: {{ $b['surface_alt'] }};
    color: {{ $b['primary_dark'] }};
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    padding: 9px 12px;
    border-bottom: 1px solid {{ $b['border'] }};
    text-align: left;
}

table.items td {
    padding: 9px 12px;
    border-bottom: 1px solid {{ $b['surface'] }};
    color: {{ $b['text'] }};
}

table.items tbody tr:nth-child(even) td { background: rgba(244, 246, 239, 0.55); }
table.items tbody tr:last-child td { border-bottom: none; }
table.items td.amount, table.items th.amount { text-align: right; white-space: nowrap; }

.empty-row td {
    text-align: center;
    color: {{ $b['text_muted'] }};
    font-style: italic;
    padding: 18px 12px;
    background: transparent !important;
}

.work-box {
    min-height: 80px;
    background: {{ $b['surface'] }};
    border: 1px solid {{ $b['border'] }};
    border-radius: 6px;
    padding: 12px 14px;
    font-size: 13px;
    line-height: 1.6;
    white-space: pre-wrap;
    color: {{ $b['text'] }};
}

.totals-wrap {
    display: flex;
    justify-content: flex-end;
    margin-top: 24px;
    margin-bottom: 28px;
}

table.totals { width: 340px; border-collapse: collapse; font-size: 14px; }
table.totals td { padding: 8px 14px; border: 1px solid {{ $b['border'] }}; }
table.totals .label { font-weight: 600; color: {{ $b['text_muted'] }}; background: {{ $b['surface'] }}; width: 65%; }
table.totals .value { text-align: right; font-weight: 600; color: {{ $b['text'] }}; width: 35%; }

table.totals tr.grand td {
    background: linear-gradient(135deg, {{ $b['primary_light'] }} 0%, {{ $b['primary'] }} 100%);
    color: #fff;
    font-size: 15px;
    font-weight: 700;
    border-color: {{ $b['primary_dark'] }};
    padding: 11px 14px;
}

table.totals tr.grand .label { background: transparent; color: #fff; }

.footer {
    border-top: 1px solid {{ $b['border'] }};
    padding-top: 20px;
    text-align: center;
}

.footer p {
    font-size: 13px;
    color: {{ $b['text_muted'] }};
    margin-bottom: 6px;
    line-height: 1.6;
}

.footer .thank-you {
    margin-top: 14px;
    font-family: 'Rajdhani', 'Inter', sans-serif;
    font-size: 16px;
    font-weight: 700;
    color: {{ $b['primary_dark'] }};
    letter-spacing: 0.02em;
}

@media print {
    body.invoice-document-body { background: #fff; padding: 0; }
    .no-print { display: none !important; }
    .invoice-sheet { box-shadow: none; border: none; border-radius: 0; padding: 24px; max-width: 100%; }
}

@media (max-width: 680px) {
    .invoice-sheet { padding: 24px 20px; }
    .header { flex-direction: column; }
    .invoice-meta { text-align: left; }
    .grid-2 { grid-template-columns: 1fr; }
    .totals-wrap { justify-content: stretch; }
    table.totals { width: 100%; }
}
