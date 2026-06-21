<?php

namespace App\Services;

use App\Filament\Resources\BookingResource;
use App\Filament\Resources\QuoteRequestResource;
use App\Mail\BookingAdminNotifyMail;
use App\Mail\BookingReceivedMail;
use App\Mail\QuoteAdminNotifyMail;
use App\Mail\QuoteReceivedMail;
use App\Models\Booking;
use App\Models\QuoteRequest;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AdminNotificationService
{
    public function notifyNewBooking(Booking $booking): void
    {
        $booking->load(['service', 'vehicle']);

        $this->sendMailSafely(fn () => Mail::to($booking->customer_email)->send(new BookingReceivedMail($booking)));

        $adminEmail = config('mail.admin_address');
        if ($adminEmail) {
            $this->sendMailSafely(fn () => Mail::to($adminEmail)->send(new BookingAdminNotifyMail($booking)));
        }

        $recipients = User::whereIn('role', ['admin', 'staff'])->get();
        if ($recipients->isNotEmpty()) {
            Notification::make()
                ->title('New booking — '.$booking->reference)
                ->body(
                    "Customer: {$booking->customer_name}\n".
                    'Email: '.$booking->customer_email."\n".
                    'Phone: '.$booking->customer_phone."\n".
                    'Service: '.($booking->service?->name ?? '—')."\n".
                    'When: '.$booking->scheduled_date->format('M j, Y').' at '.substr((string) $booking->scheduled_time, 0, 5)
                )
                ->icon('heroicon-o-calendar-days')
                ->iconColor('success')
                ->color('success')
                ->actions([
                    Action::make('view')
                        ->label('Open booking')
                        ->button()
                        ->url(BookingResource::getUrl('edit', ['record' => $booking])),
                ])
                ->sendToDatabase($recipients);
        }
    }

    public function notifyNewQuote(QuoteRequest $quote): void
    {
        $quote->load('service');

        $this->sendMailSafely(fn () => Mail::to($quote->email)->send(new QuoteReceivedMail($quote)));

        $adminEmail = config('mail.admin_address');
        if ($adminEmail) {
            $this->sendMailSafely(fn () => Mail::to($adminEmail)->send(new QuoteAdminNotifyMail($quote)));
        }

        $recipients = User::whereIn('role', ['admin', 'staff'])->get();
        if ($recipients->isNotEmpty()) {
            Notification::make()
                ->title('New quote request — '.$quote->name)
                ->body(
                    'Email: '.$quote->email."\n".
                    'Phone: '.$quote->phone."\n".
                    'Service: '.($quote->service?->name ?? 'General inquiry')."\n".
                    ($quote->message ? 'Message: '.Str::limit($quote->message, 120) : '')
                )
                ->icon('heroicon-o-inbox')
                ->iconColor('warning')
                ->color('warning')
                ->actions([
                    Action::make('view')
                        ->label('Open quote')
                        ->button()
                        ->url(QuoteRequestResource::getUrl('edit', ['record' => $quote])),
                ])
                ->sendToDatabase($recipients);
        }
    }

    private function sendMailSafely(callable $sender): void
    {
        try {
            $sender();
        } catch (\Throwable $e) {
            Log::warning('Mail send failed: '.$e->getMessage());
        }
    }
}
