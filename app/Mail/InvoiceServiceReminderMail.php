<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceServiceReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public string $reminderType = 'early',
    ) {
        $this->invoice->load(['user', 'vehicle']);
    }

    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address');
        $fromName = config('neamee.company_name');

        $subject = $this->reminderType === 'due'
            ? 'Today is your service day — '.$fromName
            : 'Upcoming service reminder — '.$fromName;

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            replyTo: [new Address($fromAddress, $fromName)],
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.invoice-service-reminder',
            with: [
                'invoice' => $this->invoice,
                'reminderType' => $this->reminderType,
            ],
        );
    }
}
