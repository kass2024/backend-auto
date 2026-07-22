<div class="invoice-sheet">
    <div class="header">
        <div class="brand">
            @if(!empty($showLogo))
                <img src="{{ $logoUrl }}" alt="{{ config('neamee.company_name') }}">
            @endif
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
            @if($invoice->paymentMethodLabel())
                <div class="meta-row"><strong>Payment method:</strong> {{ $invoice->paymentMethodLabel() }}</div>
            @endif
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
                        <th>Part name</th>
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
            <div class="panel-title">Labor</div>
            <table class="items">
                <thead>
                    <tr>
                        <th>Description</th>
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
                        <tr class="empty-row"><td colspan="3">No labor listed</td></tr>
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
                <td class="label">Labor</td>
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
        @php
            $paymentDetails = $paymentDetails ?? $invoice->paymentMethodDetails();
        @endphp

        @if($invoice->paymentMethodLabel())
            <div class="pay-method-box">
                <p class="pay-method-title"><strong>Pay with {{ $paymentDetails['title'] }}</strong></p>
                @foreach($paymentDetails['lines'] as $line)
                    <p>{{ $line }}</p>
                @endforeach
                @if(!empty($paymentDetails['link']))
                    <p style="margin-top:8px;">
                        <a href="{{ $paymentDetails['link'] }}" class="invoice-stripe-cta" style="display:inline-block;">
                            Open Cash App — {{ $paymentDetails['title'] }}
                        </a>
                    </p>
                    <p style="font-size:12px;word-break:break-all;">{{ $paymentDetails['link'] }}</p>
                @endif
            </div>
        @else
            <p>Payment methods: Cash, Check, Bank Transfer, Credit Card (Stripe), Mobile Money, Zelle, Cash App.</p>
        @endif

        @if($invoice->isPaid())
            <p>
                Paid on {{ $invoice->paid_at?->format('M j, Y g:i A') }}
                @if($invoice->paymentMethodLabel())
                    via {{ $invoice->paymentMethodLabel() }}
                @endif.
            </p>
        @elseif(!empty($includeStripeLink) && !empty($paymentUrl))
            <p>Please pay by the due date using the secure Stripe link below or at our shop.</p>
            <p style="margin-top:12px;">
                <a href="{{ $paymentUrl }}" class="invoice-stripe-cta">Pay securely with Stripe — ${{ number_format($invoice->total, 2) }}</a>
            </p>
        @elseif($invoice->paymentMethodLabel())
            <p>Please remit payment by the due date using the details above.</p>
        @else
            <p>Please remit payment by the due date. Contact us for payment options.</p>
        @endif
        <p class="thank-you">Thank you for choosing {{ config('neamee.company_name') }}!</p>

        @php
            $qrSrc = '';
            $showPayQr = $invoice->showsPaymentQr();
            $qrKey = $qrKey ?? \App\Support\PaymentMethodDetails::qrKey($invoice->payment_method);
            if ($showPayQr) {
                if (! empty($embedQr) && ! empty($qrPath) && isset($message) && is_file($qrPath)) {
                    $qrSrc = $message->embed($qrPath);
                } elseif (! empty($qrUrl)) {
                    $qrSrc = $qrUrl;
                }
            }
        @endphp
        @if($showPayQr && $qrSrc !== '')
            <div class="pay-qr">
                @if($qrKey === 'cash_app')
                    <p class="scan-and-pay">Scan and pay with Cash App</p>
                    <p class="pay-qr-help">
                        Scan in the Cash App camera to pay
                        <strong>{{ config('neamee.payment_methods.cash_app.cashtag', '$EgideNiringiyimana') }}</strong>
                    </p>
                @else
                    <p class="scan-and-pay">Scan and pay with Zelle</p>
                    <p class="pay-qr-help">Scan this code in your bank&apos;s app to pay <strong>EGIDE</strong> via Zelle</p>
                @endif
                <img src="{{ $qrSrc }}" alt="{{ $qrKey === 'cash_app' ? 'Cash App' : 'Zelle' }} QR code" width="280" style="width:280px;max-width:100%;height:auto;border:1px solid #d8dcc8;padding:10px;background:#fff;display:block;margin:12px auto 0;">
            </div>
        @endif
    </div>
</div>
