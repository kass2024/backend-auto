<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Support\InvoiceDocument;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceViewController extends Controller
{
    public function __invoke(Request $request, Invoice $invoice): View
    {
        if (! $invoice->isValidPublicViewToken($request->query('token'))) {
            abort(403, 'This invoice link is invalid or has expired. Please request a new invoice email from us.');
        }

        return view('invoices.print', InvoiceDocument::viewData($invoice, includeStripeLink: false, showLogo: true));
    }
}
