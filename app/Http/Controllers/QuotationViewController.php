<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Services\QuotationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuotationViewController extends Controller
{
    public function show(Request $request, Quotation $quotation): View
    {
        if (! $quotation->isValidPublicViewToken($request->query('token'))) {
            abort(403, 'This quote link is invalid or has expired. Please request a new quote from us.');
        }

        app(QuotationService::class)->markViewed($quotation);
        $quotation->refresh()->load(['items', 'user', 'vehicle']);

        return view('quotations.public', [
            'quotation' => $quotation,
            'token' => $request->query('token'),
            'logoUrl' => asset(config('neamee.logo', 'images/logo/logo.png')),
        ]);
    }

    public function print(Request $request, Quotation $quotation): View
    {
        if (! $quotation->isValidPublicViewToken($request->query('token'))) {
            abort(403, 'This quote link is invalid or has expired.');
        }

        $quotation->load(['items', 'user', 'vehicle']);

        return view('quotations.print', [
            'quotation' => $quotation,
            'logoUrl' => asset(config('neamee.logo', 'images/logo/logo.png')),
            'autoPrint' => $request->boolean('autoprint'),
        ]);
    }

    public function sign(Request $request, Quotation $quotation): RedirectResponse
    {
        if (! $quotation->isValidPublicViewToken($request->query('token'))) {
            abort(403, 'This quote link is invalid or has expired.');
        }

        $validated = $request->validate([
            'signature_name' => ['required', 'string', 'max:255'],
            'signature_data' => ['required', 'string', 'max:500000'],
        ]);

        try {
            app(QuotationService::class)->acceptWithSignature(
                $quotation,
                $validated['signature_name'],
                $validated['signature_data'],
                $request->ip(),
            );
        } catch (\Throwable $e) {
            return back()->withErrors(['signature' => $e->getMessage()]);
        }

        return redirect()
            ->route('quotation.view', ['quotation' => $quotation->id, 'token' => $request->query('token')])
            ->with('status', 'Thank you — your e-signature has been saved. We will begin work as agreed.');
    }
}
