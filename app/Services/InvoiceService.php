<?php

namespace App\Services;

use App\Mail\InvoiceSentMail;
use App\Models\Invoice;
use App\Models\JobCard;
use Illuminate\Support\Facades\Mail;

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
        $subtotal = $invoice->items->sum('total');
        $taxAmount = round($subtotal * ((float) $invoice->tax_rate / 100), 2);
        $total = max(0, $subtotal + $taxAmount - (float) $invoice->discount);

        $invoice->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $total,
        ]);
    }

    public function createFromJobCard(JobCard $jobCard): Invoice
    {
        $jobCard->load(['user', 'invoice', 'lines.service', 'service']);

        if ($jobCard->invoice) {
            $invoice = $jobCard->invoice;
            $invoice->items()->delete();
        } else {
            $invoice = Invoice::create([
                'invoice_number' => $this->generateNumber(),
                'user_id' => $jobCard->user_id,
                'job_card_id' => $jobCard->id,
                'subtotal' => 0,
                'tax_rate' => 0,
                'tax_amount' => 0,
                'discount' => 0,
                'total' => 0,
                'status' => 'draft',
                'due_date' => now()->addDays(14),
            ]);
        }

        foreach ($jobCard->lines as $line) {
            $invoice->items()->create([
                'description' => $line->description,
                'quantity' => $line->quantity,
                'unit_price' => $line->unit_price,
                'total' => $line->total,
            ]);
        }

        if ($jobCard->parts_cost > 0) {
            $invoice->items()->create([
                'description' => 'Parts — Job '.$jobCard->job_number,
                'quantity' => 1,
                'unit_price' => $jobCard->parts_cost,
                'total' => $jobCard->parts_cost,
            ]);
        }

        if ($invoice->items()->count() === 0 && $jobCard->total_cost > 0) {
            $primary = $jobCard->service?->name ?? 'Repair services';
            $invoice->items()->create([
                'description' => $primary.' — Job '.$jobCard->job_number,
                'quantity' => 1,
                'unit_price' => $jobCard->total_cost,
                'total' => $jobCard->total_cost,
            ]);
        }

        $this->recalculateTotals($invoice);

        return $invoice->fresh(['items', 'user']);
    }

    public function sendToCustomer(Invoice $invoice): void
    {
        $invoice->load(['items', 'user']);

        if (! $invoice->user?->email) {
            throw new \RuntimeException('Customer has no email address.');
        }

        Mail::to($invoice->user->email)->send(new InvoiceSentMail($invoice));

        $invoice->update([
            'status' => $invoice->status === 'paid' ? 'paid' : 'sent',
        ]);
    }
}
