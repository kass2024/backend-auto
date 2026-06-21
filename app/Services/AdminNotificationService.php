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
                ->title('New booking: '.$booking->reference)
                ->body($booking->customer_name.' · '.($booking->service?->name ?? 'Service').' · '.$booking->scheduled_date->format('M j').' at '.substr((string) $booking->scheduled_time, 0, 5))
                ->icon('heroicon-o-calendar-days')
                ->success()
                ->actions([
                    Action::make('view')
                        ->label('Open booking')
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
                ->title('New quote request')
                ->body($quote->name.' · '.($quote->service?->name ?? 'General inquiry'))
                ->icon('heroicon-o-inbox')
                ->info()
                ->actions([
                    Action::make('view')
                        ->label('Open quote')
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
