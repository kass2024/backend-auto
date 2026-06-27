<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\View\View;

class InvoicePrintController extends Controller
{
    public function __invoke(Invoice $invoice): View
    {
        $invoice->load([
            'user',
            'vehicle',
            'jobCard.vehicle',
            'items.part',
            'items.service',
        ]);

        return view('invoices.print', [
            'invoice' => $invoice,
            'parts' => $invoice->items->where('type', 'part'),
            'services' => $invoice->items->where('type', 'service'),
        ]);
    }
}
