<?php

namespace App\Http\Controllers\Admin;

use App\Filament\Resources\InvoiceResource;
use App\Filament\Support\InvoiceFlashNotifications;
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
            $body = $sent->wantsStripePayment() && ! $sent->isPaid()
                ? 'Invoice '.$sent->invoice_number.' was sent to '.$sent->user?->email.' with Stripe payment link. Check inbox and spam folder.'
                : 'Invoice '.$sent->invoice_number.' was sent to '.$sent->user?->email.' (no Stripe link). Check inbox and spam folder.';

            InvoiceFlashNotifications::flash('success', $wasEmailedBefore ? 'Invoice resent successfully' : 'Invoice email sent successfully', $body);
        } catch (\Throwable $e) {
            InvoiceFlashNotifications::flash('danger', 'Email failed', $e->getMessage());
        }

        return redirect()->to(InvoiceResource::getUrl('index'));
    }

    public function markPaid(Invoice $invoice): RedirectResponse
    {
        try {
            app(InvoiceService::class)->markPaid($invoice);

            InvoiceFlashNotifications::flash('success', 'Invoice marked as paid', 'The invoice status has been updated.');
        } catch (\Throwable $e) {
            InvoiceFlashNotifications::flash('danger', 'Could not mark invoice as paid', $e->getMessage());
        }

        return redirect()->to(InvoiceResource::getUrl('index'));
    }

    public function markUnpaid(Invoice $invoice): RedirectResponse
    {
        try {
            app(InvoiceService::class)->markUnpaid($invoice);

            InvoiceFlashNotifications::flash('success', 'Invoice marked as unpaid', 'The invoice status has been updated.');
        } catch (\Throwable $e) {
            InvoiceFlashNotifications::flash('danger', 'Could not mark invoice as unpaid', $e->getMessage());
        }

        return redirect()->to(InvoiceResource::getUrl('index'));
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        try {
            $invoice->delete();

            InvoiceFlashNotifications::flash('success', 'Invoice deleted successfully', 'The invoice has been removed.');
        } catch (\Throwable $e) {
            InvoiceFlashNotifications::flash('danger', 'Delete failed', $e->getMessage());
        }

        return redirect()->to(InvoiceResource::getUrl('index'));
    }
}
