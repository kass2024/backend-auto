<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Support\InvoiceDocument;
use Illuminate\View\View;

class InvoiceViewController extends Controller
{
    public function __invoke(Invoice $invoice): View
    {
        return view('invoices.print', InvoiceDocument::viewData($invoice, includeStripeLink: false, showLogo: true));
    }
}
