<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use Illuminate\View\View;

class QuotationPrintController extends Controller
{
    public function __invoke(Quotation $quotation): View
    {
        $quotation->load(['items', 'user', 'vehicle']);

        return view('quotations.print', [
            'quotation' => $quotation,
            'logoUrl' => asset(config('neamee.logo', 'images/logo/logo.png')),
            'autoPrint' => request()->boolean('autoprint'),
        ]);
    }
}
