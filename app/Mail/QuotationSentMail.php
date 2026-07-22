<?php

namespace App\Mail;

use App\Models\Quotation;
use App\Support\InvoiceDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuotationSentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Quotation $quotation)
    {
        $this->quotation->loadMissing(['items', 'user', 'vehicle']);
        $this->quotation->ensurePublicViewToken();
    }

    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address');
        $fromName = config('neamee.company_name');

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            replyTo: [new Address($fromAddress, $fromName)],
            subject: 'Repair quote '.$this->quotation->quote_number.' from '.$fromName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.quotation-html',
            text: 'emails.quotation-text',
            with: [
                'quotation' => $this->quotation,
                'quoteUrl' => InvoiceDocument::emailUrl(
                    fn (): string => $this->quotation->publicUrl()
                ),
                'logoUrl' => InvoiceDocument::logoUrl(forEmail: true),
            ],
        );
    }
}
