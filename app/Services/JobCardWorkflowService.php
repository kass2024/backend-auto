<?php

namespace App\Services;

use App\Models\JobCard;
use App\Models\Service;

class JobCardWorkflowService
{
    public function generateJobNumber(): string
    {
        $year = now()->year;
        $last = JobCard::whereYear('created_at', $year)->orderByDesc('id')->value('job_number');
        $seq = 1;
        if ($last && preg_match('/-(\d+)$/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return sprintf('JOB-%s-%04d', $year, $seq);
    }

    public function recalculateTotals(JobCard $jobCard): void
    {
        $jobCard->load('lines');

        $servicesTotal = $jobCard->lines->sum('total');
        $partsCost = (float) $jobCard->parts_cost;
        $laborCost = $servicesTotal > 0 ? $servicesTotal : (float) $jobCard->labor_cost;
        $total = $laborCost + $partsCost;

        $primaryServiceId = $jobCard->lines->first()?->service_id ?? $jobCard->service_id;

        $jobCard->update([
            'service_id' => $primaryServiceId,
            'labor_cost' => $laborCost,
            'total_cost' => $total,
        ]);
    }

    public function syncLinesFromForm(JobCard $jobCard, array $lineItems): void
    {
        $jobCard->lines()->delete();

        foreach ($lineItems as $item) {
            if (empty($item['description']) && empty($item['service_id'])) {
                continue;
            }

            $service = ! empty($item['service_id'])
                ? Service::find($item['service_id'])
                : null;

            $qty = max(1, (int) ($item['quantity'] ?? 1));
            $unitPrice = (float) ($item['unit_price'] ?? $service?->price_from ?? 0);
            $description = $item['description'] ?? $service?->name ?? 'Service';

            $jobCard->lines()->create([
                'service_id' => $service?->id,
                'description' => $description,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'total' => round($qty * $unitPrice, 2),
            ]);
        }

        $this->recalculateTotals($jobCard->fresh());
    }

    public function autoInvoiceIfReady(JobCard $jobCard): ?\App\Models\Invoice
    {
        if (! in_array($jobCard->status, ['ready_for_pickup', 'delivered'], true)) {
            return null;
        }

        if ($jobCard->total_cost <= 0) {
            return null;
        }

        return app(InvoiceService::class)->createFromJobCard($jobCard->fresh(['lines', 'user', 'invoice']));
    }
}
