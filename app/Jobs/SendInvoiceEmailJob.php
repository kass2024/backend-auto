<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendInvoiceEmailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $invoiceId) {}

    public function handle(InvoiceService $invoiceService): void
    {
        $invoice = Invoice::with(['items', 'user', 'vehicle', 'jobCard.vehicle'])->find($this->invoiceId);

        if (! $invoice) {
            throw new \RuntimeException('Invoice not found.');
        }

        $invoiceService->deliverInvoiceEmail($invoice);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('SendInvoiceEmailJob failed permanently', [
            'invoice_id' => $this->invoiceId,
            'message' => $exception->getMessage(),
        ]);
    }
}
