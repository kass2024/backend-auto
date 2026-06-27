<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Support\InvoiceDocument;
use Illuminate\View\View;

class InvoicePrintController extends Controller
{
    public function __invoke(Invoice $invoice): View
    {
        return view('invoices.print', InvoiceDocument::viewData($invoice, includeStripeLink: false));
    }
}
