<?php

namespace App\Services;

use App\Mail\QuotationSentMail;
use App\Models\Invoice;
use App\Models\Part;
use App\Models\Quotation;
use App\Models\Service;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class QuotationService
{
    public function generateNumber(): string
    {
        $year = now()->year;
        $last = Quotation::whereYear('created_at', $year)->orderByDesc('id')->value('quote_number');
        $seq = 1;

        if ($last && preg_match('/-(\d+)$/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return sprintf('QT-%d-%04d', $year, $seq);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $partLines
     * @param  array<int, array<string, mixed>>  $laborLines
     * @param  array<int, array<string, mixed>>  $additionalLines
     */
    public function createFromForm(
        array $data,
        array $partLines,
        array $laborLines,
        array $additionalLines,
    ): Quotation {
        $data = $this->resolveCustomerAndVehicle($data);
        $data['quote_number'] = $data['quote_number'] ?? $this->generateNumber();
        $data['issued_at'] = $data['issued_at'] ?? now()->toDateString();
        $data['expires_at'] = $data['expires_at'] ?? now()->addDays(14)->toDateString();
        $data['payment_terms'] = $data['payment_terms'] ?: Quotation::defaultPaymentTerms();
        $data['warranty_terms'] = $data['warranty_terms'] ?: Quotation::defaultWarrantyTerms();
        $data['authorization_terms'] = $data['authorization_terms'] ?: Quotation::defaultAuthorizationTerms();
        $data['status'] = $data['status'] ?? 'draft';

        $quotation = Quotation::create($this->quotationAttributes($data));
        $this->syncLineItems($quotation, $partLines, $laborLines, $additionalLines);
        $this->recalculateTotals($quotation);
        $quotation->ensurePublicViewToken();

        return $quotation->fresh(['items', 'user', 'vehicle']);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $partLines
     * @param  array<int, array<string, mixed>>  $laborLines
     * @param  array<int, array<string, mixed>>  $additionalLines
     */
    public function updateFromForm(
        Quotation $quotation,
        array $data,
        array $partLines,
        array $laborLines,
        array $additionalLines,
    ): Quotation {
        if ($quotation->isAccepted() || $quotation->status === 'converted') {
            throw new \RuntimeException('Accepted or converted quotes cannot be edited.');
        }

        $data = $this->resolveCustomerAndVehicle($data);
        $quotation->update($this->quotationAttributes($data));
        $this->syncLineItems($quotation, $partLines, $laborLines, $additionalLines);
        $this->recalculateTotals($quotation);

        return $quotation->fresh(['items', 'user', 'vehicle']);
    }

    /**
     * @param  array<int, array<string, mixed>>  $partLines
     * @param  array<int, array<string, mixed>>  $laborLines
     * @param  array<int, array<string, mixed>>  $additionalLines
     */
    public function syncLineItems(
        Quotation $quotation,
        array $partLines,
        array $laborLines,
        array $additionalLines,
    ): void {
        $quotation->items()->delete();
        $sort = 0;

        foreach (['part' => $partLines, 'labor' => $laborLines, 'additional' => $additionalLines] as $type => $lines) {
            foreach ($lines as $line) {
                if (! filled($line['description'] ?? null) && ! filled($line['part_id'] ?? null) && ! filled($line['service_id'] ?? null)) {
                    continue;
                }

                $quantity = max(0.01, (float) ($line['quantity'] ?? 1));
                $unitPrice = (float) ($line['unit_price'] ?? 0);
                $total = round((float) ($line['total'] ?? ($quantity * $unitPrice)), 2);
                $description = $line['description'] ?? 'Item';

                if ($type === 'part' && filled($line['part_id'] ?? null) && ! filled($line['description'] ?? null)) {
                    $part = Part::find($line['part_id']);
                    $description = $part?->name ?: ($part?->part_no ?: 'Part');
                }

                if ($type === 'labor' && filled($line['service_id'] ?? null) && ! filled($line['description'] ?? null)) {
                    $service = Service::find($line['service_id']);
                    $description = $service?->name ?: 'Labor';
                }

                $quotation->items()->create([
                    'type' => $type,
                    'part_id' => $type === 'part' && filled($line['part_id'] ?? null) ? (int) $line['part_id'] : null,
                    'service_id' => $type === 'labor' && filled($line['service_id'] ?? null) ? (int) $line['service_id'] : null,
                    'description' => $description,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total' => $total,
                    'sort_order' => $sort++,
                ]);
            }
        }
    }

    public function recalculateTotals(Quotation $quotation): void
    {
        $quotation->load('items');
        $partsTotal = round($quotation->items->where('type', 'part')->sum('total'), 2);
        $laborTotal = round($quotation->items->where('type', 'labor')->sum('total'), 2);
        $additionalTotal = round($quotation->items->where('type', 'additional')->sum('total'), 2);
        $subtotal = round($partsTotal + $laborTotal + $additionalTotal, 2);
        $discount = (float) $quotation->discount;
        $taxRate = (float) $quotation->tax_rate;
        $taxAmount = round(max(0, $subtotal - $discount) * ($taxRate / 100), 2);
        $total = max(0, round($subtotal - $discount + $taxAmount, 2));

        $quotation->update([
            'parts_total' => $partsTotal,
            'labor_total' => $laborTotal,
            'additional_total' => $additionalTotal,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $total,
        ]);
    }

    public function sendToCustomer(Quotation $quotation): void
    {
        $quotation->load(['user', 'items']);
        $email = $quotation->customer_email ?: $quotation->user?->email;

        if (! $email) {
            throw new \RuntimeException('Quote has no customer email address.');
        }

        $quotation->ensurePublicViewToken();

        if (in_array($quotation->status, ['draft', 'viewed'], true)) {
            $quotation->update(['status' => 'sent']);
        }

        $quotationId = $quotation->id;

        dispatch(function () use ($quotationId): void {
            $quote = Quotation::with(['items', 'user', 'vehicle'])->find($quotationId);
            if (! $quote) {
                return;
            }

            try {
                app(QuotationService::class)->deliverEmail($quote);
            } catch (Throwable $e) {
                Log::error('Background quotation email failed', [
                    'quotation_id' => $quotationId,
                    'message' => $e->getMessage(),
                ]);
            }
        })->afterResponse();
    }

    public function deliverEmail(Quotation $quotation): void
    {
        $email = $quotation->customer_email ?: $quotation->user?->email;

        if (! $email) {
            throw new \RuntimeException('Quote has no customer email address.');
        }

        $quotation->ensurePublicViewToken();

        Mail::to($email)->send(new QuotationSentMail($quotation));

        $quotation->update(['customer_emailed_at' => now()]);

        if ($quotation->status === 'draft') {
            $quotation->update(['status' => 'sent']);
        }
    }

    public function markViewed(Quotation $quotation): void
    {
        if ($quotation->status === 'sent') {
            $quotation->update(['status' => 'viewed']);
        }

        if ($quotation->isExpired() && $quotation->status !== 'expired') {
            $quotation->update(['status' => 'expired']);
        }
    }

    public function acceptWithSignature(
        Quotation $quotation,
        string $signatureName,
        string $signatureData,
        ?string $ip = null,
    ): Quotation {
        if (! $quotation->canBeSigned()) {
            throw new \RuntimeException('This quote can no longer be signed.');
        }

        if (! str_starts_with($signatureData, 'data:image')) {
            throw new \RuntimeException('Invalid signature data.');
        }

        $quotation->update([
            'signature_name' => trim($signatureName),
            'signature_data' => $signatureData,
            'signed_at' => now(),
            'signer_ip' => $ip,
            'status' => 'accepted',
        ]);

        return $quotation->fresh();
    }

    public function convertToInvoice(Quotation $quotation): Invoice
    {
        if ($quotation->invoice_id || $quotation->status === 'converted') {
            throw new \RuntimeException('This quote already has an invoice.');
        }

        if (! $quotation->isAccepted()) {
            throw new \RuntimeException('Customer must e-sign the quote before creating an invoice.');
        }

        $quotation->load(['items', 'user', 'vehicle']);
        $invoiceService = app(InvoiceService::class);

        $userId = $quotation->user_id;
        if (! $userId) {
            $user = $this->ensureCustomerUser($quotation);
            $userId = $user->id;
            $quotation->update(['user_id' => $userId]);
        }

        $partLines = $quotation->partItems->map(fn ($item) => [
            'part_id' => $item->part_id,
            'part_number' => $item->part?->part_no ?? $item->part?->sku,
            'description' => $item->description,
            'quantity' => max(1, (int) ceil((float) $item->quantity)),
            'unit_price' => $item->unit_price,
            'total' => $item->total,
        ])->all();

        $serviceLines = $quotation->laborItems->map(fn ($item) => [
            'service_id' => $item->service_id,
            'description' => $item->description,
            'quantity' => max(1, (int) ceil((float) $item->quantity)),
            'unit_price' => $item->unit_price,
            'total' => $item->total,
        ])->all();

        foreach ($quotation->additionalItems as $item) {
            $serviceLines[] = [
                'service_id' => null,
                'description' => $item->description,
                'quantity' => max(1, (int) ceil((float) $item->quantity)),
                'unit_price' => $item->unit_price,
                'total' => $item->total,
            ];
        }

        $invoice = Invoice::create([
            'invoice_number' => $invoiceService->generateNumber(),
            'user_id' => $userId,
            'vehicle_id' => $quotation->vehicle_id,
            'quotation_id' => $quotation->id,
            'subtotal' => $quotation->subtotal,
            'labor_total' => round((float) $quotation->labor_total + (float) $quotation->additional_total, 2),
            'parts_total' => $quotation->parts_total,
            'tax_rate' => $quotation->tax_rate,
            'tax_amount' => $quotation->tax_amount,
            'discount' => $quotation->discount,
            'total' => $quotation->total,
            'status' => 'sent',
            'payment_method' => 'cash',
            'paid_at' => null,
            'due_date' => now()->addDays(14)->toDateString(),
            'work_description' => collect([
                $quotation->problem_description ? 'Problem: '.$quotation->problem_description : null,
                $quotation->inspection_findings ? 'Findings: '.$quotation->inspection_findings : null,
                $quotation->proposed_repairs ? 'Proposed: '.$quotation->proposed_repairs : null,
            ])->filter()->implode("\n\n"),
        ]);

        $invoiceService->syncLineItems($invoice, $partLines, $serviceLines);
        $invoiceService->recalculateTotals($invoice);
        $invoice->ensurePublicViewToken();

        $quotation->update([
            'invoice_id' => $invoice->id,
            'status' => 'converted',
        ]);

        return $invoice->fresh(['items', 'user', 'vehicle']);
    }

    /**
     * @return array{part_lines: array<int, array<string, mixed>>, labor_lines: array<int, array<string, mixed>>, additional_lines: array<int, array<string, mixed>>}
     */
    public function formLineData(Quotation $quotation): array
    {
        $quotation->load('items');

        $map = fn ($item) => [
            'part_id' => $item->part_id,
            'service_id' => $item->service_id,
            'description' => $item->description,
            'quantity' => $item->quantity,
            'unit_price' => $item->unit_price,
            'total' => $item->total,
        ];

        return [
            'part_lines' => $quotation->partItems->map($map)->values()->all(),
            'labor_lines' => $quotation->laborItems->map($map)->values()->all(),
            'additional_lines' => $quotation->additionalItems->map($map)->values()->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function resolveCustomerAndVehicle(array $data): array
    {
        if (filled($data['user_id'] ?? null)) {
            $user = User::find($data['user_id']);
            if ($user) {
                $data['customer_name'] = $data['customer_name'] ?: $user->name;
                $data['customer_email'] = $data['customer_email'] ?: $user->email;
                $data['customer_phone'] = $data['customer_phone'] ?: $user->phone;
            }
        }

        if (filled($data['vehicle_id'] ?? null)) {
            $vehicle = Vehicle::find($data['vehicle_id']);
            if ($vehicle) {
                $data['vehicle_make'] = $data['vehicle_make'] ?: $vehicle->make;
                $data['vehicle_model'] = $data['vehicle_model'] ?: $vehicle->model;
                $data['vehicle_year'] = $data['vehicle_year'] ?: $vehicle->year;
                $data['vehicle_vin'] = $data['vehicle_vin'] ?: $vehicle->vin;
                $data['vehicle_plate'] = $data['vehicle_plate'] ?: $vehicle->plate_number;
            }
        }

        if (blank($data['customer_name'] ?? null)) {
            throw new \RuntimeException('Customer name is required.');
        }

        return $data;
    }

    private function ensureCustomerUser(Quotation $quotation): User
    {
        if ($quotation->customer_email) {
            $existing = User::where('email', $quotation->customer_email)->first();
            if ($existing) {
                return $existing;
            }
        }

        return User::create([
            'name' => $quotation->customer_name,
            'email' => $quotation->customer_email ?: ('quote+'.$quotation->id.'@neamee.local'),
            'phone' => $quotation->customer_phone,
            'role' => 'customer',
            'password' => Hash::make(Str::password(12)),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function quotationAttributes(array $data): array
    {
        return collect($data)->only([
            'quote_number', 'user_id', 'vehicle_id',
            'customer_name', 'customer_phone', 'customer_email',
            'vehicle_make', 'vehicle_model', 'vehicle_year', 'vehicle_vin', 'vehicle_plate',
            'problem_description', 'inspection_findings', 'proposed_repairs',
            'discount', 'tax_rate',
            'status', 'issued_at', 'expires_at',
            'payment_terms', 'warranty_terms', 'authorization_terms',
        ])->all();
    }
}
