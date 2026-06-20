<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\AppointmentService;
use Illuminate\Console\Command;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders';

    protected $description = 'Send email reminders for appointments happening tomorrow';

    public function handle(AppointmentService $appointments): int
    {
        $tomorrow = now()->addDay()->toDateString();

        $bookings = Booking::query()
            ->with(['service', 'vehicle', 'user'])
            ->where('status', 'confirmed')
            ->whereDate('scheduled_date', $tomorrow)
            ->whereNull('reminder_sent_at')
            ->get();

        $count = 0;

        foreach ($bookings as $booking) {
            if (! $booking->customer_email) {
                continue;
            }

            $appointments->sendReminder($booking);
            $count++;
            $this->line("Reminder sent: {$booking->reference} → {$booking->customer_email}");
        }

        $this->info("Sent {$count} appointment reminder(s).");

        return self::SUCCESS;
    }
}
