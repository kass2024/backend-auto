<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Support\InvoiceDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceSentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public bool $includeStripeLink = true,
    ) {
        $this->invoice->load(['partItems', 'serviceItems', 'user', 'vehicle', 'jobCard.vehicle']);
    }

    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address');
        $fromName = config('neamee.company_name');

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            replyTo: [new Address($fromAddress, $fromName)],
            subject: 'Invoice '.$this->invoice->invoice_number.' from '.$fromName,
        );
    }

    public function content(): Content
    {
        $viewData = InvoiceDocument::viewData(
            $this->invoice,
            $this->includeStripeLink,
            showLogo: false,
            forEmail: true,
        );

        // Absolute path for $message->embed() in the Blade template (CID inline image).
        $viewData['qrPath'] = InvoiceDocument::qrAbsolutePath();
        $viewData['embedQr'] = true;

        return new Content(
            view: 'emails.invoice-html',
            text: 'emails.invoice-text',
            with: $viewData,
        );
    }
}
