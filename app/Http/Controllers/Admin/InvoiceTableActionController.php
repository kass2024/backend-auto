<?php

namespace App\Http\Controllers\Admin;

use App\Filament\Resources\InvoiceResource;
use App\Filament\Support\InvoiceEmailUi;
use App\Filament\Support\AdminFlashNotifications;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;

class InvoiceTableActionController extends Controller
{
    public function email(Invoice $invoice): RedirectResponse
    {
        try {
            $wasEmailedBefore = $invoice->wasEmailedToCustomer();

            app(InvoiceService::class)->sendToCustomer($invoice);

            $sent = $invoice->fresh();

            AdminFlashNotifications::flash(
                'success',
                InvoiceEmailUi::successTitle($wasEmailedBefore),
                InvoiceEmailUi::successBody(
                    $sent->invoice_number,
                    $sent->user?->email ?? '',
                    $sent->wantsStripePayment() && ! $sent->isPaid(),
                ),
            );
        } catch (\Throwable $e) {
            AdminFlashNotifications::flash('danger', 'Email failed', $e->getMessage());
        }

        return redirect()->to(InvoiceResource::getUrl('index'));
    }

    public function markPaid(Invoice $invoice): RedirectResponse
    {
        try {
            app(InvoiceService::class)->markPaid($invoice);

            AdminFlashNotifications::flash('success', 'Invoice marked as paid', 'The invoice status has been updated.');
        } catch (\Throwable $e) {
            AdminFlashNotifications::flash('danger', 'Could not mark invoice as paid', $e->getMessage());
        }

        return redirect()->to(InvoiceResource::getUrl('index'));
    }

    public function markUnpaid(Invoice $invoice): RedirectResponse
    {
        try {
            app(InvoiceService::class)->markUnpaid($invoice);

            AdminFlashNotifications::flash('success', 'Invoice marked as unpaid', 'The invoice status has been updated.');
        } catch (\Throwable $e) {
            AdminFlashNotifications::flash('danger', 'Could not mark invoice as unpaid', $e->getMessage());
        }

        return redirect()->to(InvoiceResource::getUrl('index'));
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        try {
            $invoice->delete();

            AdminFlashNotifications::flash('success', 'Invoice deleted successfully', 'The invoice has been removed.');
        } catch (\Throwable $e) {
            AdminFlashNotifications::flash('danger', 'Delete failed', $e->getMessage());
        }

        return redirect()->to(InvoiceResource::getUrl('index'));
    }
}
