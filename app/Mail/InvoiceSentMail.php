<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceSentMail extends Mailable
{
    use Queueable, SerializesModels;

    public ?string $paymentUrl;

    public function __construct(public Invoice $invoice)
    {
        $this->invoice->load(['partItems', 'serviceItems', 'user', 'vehicle', 'jobCard.vehicle']);
        $this->paymentUrl = $invoice->stripe_payment_url;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invoice '.$this->invoice->invoice_number.' — NEAMEE Auto-Tech Solutions',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.invoice-sent',
            with: [
                'paymentUrl' => $this->paymentUrl,
            ],
        );
    }
}
