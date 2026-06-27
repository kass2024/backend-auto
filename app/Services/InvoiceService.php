<?php

namespace App\Services;

use App\Jobs\SendInvoiceEmailJob;
use App\Mail\InvoiceSentMail;
use App\Models\Invoice;
use App\Models\JobCard;
use App\Models\Part;
use App\Services\StripePaymentService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class InvoiceService
{
    public function generateNumber(): string
    {
        $year = now()->year;
        $last = Invoice::whereYear('created_at', $year)->orderByDesc('id')->value('invoice_number');
        $seq = 1;
        if ($last && preg_match('/-(\d+)$/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return sprintf('INV-%s-%04d', $year, $seq);
    }

    public function recalculateTotals(Invoice $invoice): void
    {
        $invoice->load('items');

        $partsTotal = round($invoice->items->where('type', 'part')->sum('total'), 2);
        $laborTotal = round($invoice->items->where('type', 'service')->sum('total'), 2);
        $otherTotal = round($invoice->items->where('type', 'other')->sum('total'), 2);
        $subtotal = round($partsTotal + $laborTotal + $otherTotal, 2);
        $taxAmount = round($subtotal * ((float) $invoice->tax_rate / 100), 2);
        $total = max(0, round($subtotal + $taxAmount - (float) $invoice->discount, 2));

        $invoice->update([
            'parts_total' => $partsTotal,
            'labor_total' => $laborTotal,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $total,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $partLines
     * @param  array<int, array<string, mixed>>  $serviceLines
     */
    public function syncLineItems(Invoice $invoice, array $partLines, array $serviceLines): void
    {
        $invoice->items()->delete();

        $sort = 0;

        foreach ($partLines as $line) {
            if (! filled($line['part_id'] ?? null) && ! filled($line['description'] ?? null)) {
                continue;
            }

            $quantity = max(1, (int) ($line['quantity'] ?? 1));
            $unitPrice = (float) ($line['unit_price'] ?? 0);
            $total = round((float) ($line['total'] ?? ($quantity * $unitPrice)), 2);

            $partId = filled($line['part_id'] ?? null) ? (int) $line['part_id'] : null;
            $partNumber = $line['part_number'] ?? null;
            $description = $line['description'] ?? 'Part';

            if ($partId && ! $partNumber) {
                $part = Part::find($partId);
                $partNumber = $part?->part_no ?? $part?->sku;
                $description = $description === 'Part' ? ($part?->name ?? 'Part') : $description;
            }

            $invoice->items()->create([
                'type' => 'part',
                'part_id' => $partId,
                'part_number' => $partNumber,
                'description' => $description,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total' => $total,
                'sort_order' => $sort++,
            ]);
        }

        foreach ($serviceLines as $line) {
            if (! filled($line['description'] ?? null)) {
                continue;
            }

            $quantity = max(1, (int) ($line['quantity'] ?? 1));
            $unitPrice = (float) ($line['unit_price'] ?? 0);
            $total = round((float) ($line['total'] ?? ($quantity * $unitPrice)), 2);

            $invoice->items()->create([
                'type' => 'service',
                'service_id' => filled($line['service_id'] ?? null) ? (int) $line['service_id'] : null,
                'description' => $line['description'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total' => $total,
                'sort_order' => $sort++,
            ]);
        }
    }

    public function createFromJobCard(JobCard $jobCard): Invoice
    {
        $jobCard->load(['user', 'invoice', 'lines.service', 'service', 'vehicle']);

        if ($jobCard->invoice) {
            $invoice = $jobCard->invoice;
            $invoice->items()->delete();
        } else {
            $invoice = Invoice::create([
                'invoice_number' => $this->generateNumber(),
                'user_id' => $jobCard->user_id,
                'job_card_id' => $jobCard->id,
                'vehicle_id' => $jobCard->vehicle_id,
                'subtotal' => 0,
                'labor_total' => 0,
                'parts_total' => 0,
                'tax_rate' => 0,
                'tax_amount' => 0,
                'discount' => 0,
                'total' => 0,
                'status' => 'draft',
                'due_date' => now()->addDays(14),
                'time_received' => $jobCard->started_at,
                'time_promised' => $jobCard->completed_at,
            ]);
        }

        $lines = $this->linesFromJobCard($jobCard);
        $this->syncLineItems($invoice, $lines['part_lines'], $lines['service_lines']);
        $this->recalculateTotals($invoice);

        return $invoice->fresh(['items', 'user', 'vehicle']);
    }

    public function sendToCustomer(Invoice $invoice): void
    {
        $invoice->load(['user']);

        if (! $invoice->user?->email) {
            throw new \RuntimeException('Customer has no email address.');
        }

        if ($invoice->status !== 'paid') {
            $invoice->update(['status' => 'sent']);
        }

        $this->deliverInvoiceEmail($invoice->fresh());
    }

    public function deliverInvoiceEmail(Invoice $invoice): void
    {
        $invoice->load(['items', 'user', 'vehicle', 'jobCard.vehicle']);

        if (! $invoice->user?->email) {
            throw new \RuntimeException('Customer has no email address.');
        }

        $recipient = $invoice->user->email;
        $includeStripeLink = $invoice->wantsStripePayment() && ! $invoice->isPaid() && $invoice->total > 0;

        if ($includeStripeLink) {
            try {
                app(StripePaymentService::class)->refreshCheckoutSession($invoice);
                $invoice->refresh();
            } catch (Throwable $e) {
                Log::warning('Stripe checkout session failed for invoice '.$invoice->id.': '.$e->getMessage());
            }
        }

        $invoice->ensurePublicViewToken();
        $invoice = $invoice->fresh();

        try {
            Mail::to($recipient)->send(
                new InvoiceSentMail($invoice, $includeStripeLink)
            );
        } catch (Throwable $e) {
            Log::error('Invoice email failed', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'recipient' => $recipient,
                'message' => $e->getMessage(),
            ]);

            throw new \RuntimeException('Could not send invoice email: '.$e->getMessage(), 0, $e);
        }

        Log::info('Invoice email sent', [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'recipient' => $recipient,
        ]);

        $invoice->markEmailedToCustomer();
    }

    public function queueInvoiceEmail(Invoice $invoice): void
    {
        if (config('queue.default') === 'sync') {
            $this->deliverInvoiceEmail($invoice);

            return;
        }

        SendInvoiceEmailJob::dispatch($invoice->id)->afterResponse();
    }

    public function markPaid(Invoice $invoice, ?string $paymentMethod = null): void
    {
        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_method' => $paymentMethod ?? $invoice->payment_method ?? 'cash',
        ]);
    }

    public function markUnpaid(Invoice $invoice): void
    {
        $invoice->update([
            'status' => 'sent',
            'paid_at' => null,
            'payment_method' => null,
        ]);
    }

    /**
     * @return array{part_lines: array<int, array<string, mixed>>, service_lines: array<int, array<string, mixed>>}
     */
    public function linesFromJobCard(JobCard $jobCard): array
    {
        $partLines = [];
        $serviceLines = [];

        foreach ($jobCard->lines as $line) {
            $serviceLines[] = [
                'service_id' => $line->service_id,
                'description' => $line->description,
                'quantity' => $line->quantity,
                'unit_price' => $line->unit_price,
                'total' => $line->total,
            ];
        }

        if ($jobCard->parts_cost > 0) {
            $partLines[] = [
                'part_id' => null,
                'part_number' => '—',
                'description' => 'Parts — Job '.$jobCard->job_number,
                'quantity' => 1,
                'unit_price' => $jobCard->parts_cost,
                'total' => $jobCard->parts_cost,
            ];
        }

        if ($serviceLines === [] && $partLines === [] && $jobCard->total_cost > 0) {
            $primary = $jobCard->service?->name ?? 'Repair services';
            $serviceLines[] = [
                'service_id' => $jobCard->service_id,
                'description' => $primary.' — Job '.$jobCard->job_number,
                'quantity' => 1,
                'unit_price' => $jobCard->total_cost,
                'total' => $jobCard->total_cost,
            ];
        }

        return [
            'part_lines' => $partLines,
            'service_lines' => $serviceLines,
        ];
    }

    /**
     * @return array{part_lines: array<int, array<string, mixed>>, service_lines: array<int, array<string, mixed>>}
     */
    public function formLineData(Invoice $invoice): array
    {
        $invoice->load('items');

        return [
            'part_lines' => $invoice->items->where('type', 'part')->values()->map(fn ($item) => [
                'part_id' => $item->part_id,
                'part_number' => $item->part_number,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total' => $item->total,
            ])->all(),
            'service_lines' => $invoice->items->where('type', 'service')->values()->map(fn ($item) => [
                'service_id' => $item->service_id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total' => $item->total,
            ])->all(),
        ];
    }
}
