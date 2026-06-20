<?php

namespace App\Services;

use App\Mail\AppointmentReminderMail;
use App\Mail\AppointmentScheduledMail;
use App\Models\Booking;
use App\Models\JobCard;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AppointmentService
{
    public function generateReference(): string
    {
        $year = now()->year;
        $last = Booking::whereYear('created_at', $year)->orderByDesc('id')->value('reference');
        $seq = 1;
        if ($last && preg_match('/-(\d+)$/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return sprintf('BK-%s-%04d', $year, $seq);
    }

    public function scheduleFromJobCard(JobCard $jobCard, array $data, ?User $staff = null): Booking
    {
        $jobCard->load(['user', 'vehicle', 'service']);

        $booking = Booking::create([
            'reference' => $this->generateReference(),
            'user_id' => $jobCard->user_id,
            'vehicle_id' => $jobCard->vehicle_id,
            'service_id' => $data['service_id'] ?? $jobCard->service_id,
            'mechanic_id' => $data['mechanic_id'] ?? $jobCard->mechanic_id,
            'job_card_id' => $jobCard->id,
            'scheduled_by_user_id' => $staff?->id,
            'customer_name' => $jobCard->user?->name ?? 'Customer',
            'customer_email' => $jobCard->user?->email ?? '',
            'customer_phone' => $jobCard->user?->phone ?? '',
            'scheduled_date' => $data['scheduled_date'],
            'scheduled_time' => $data['scheduled_time'],
            'notes' => $data['notes'] ?? null,
            'staff_notes' => $data['staff_notes'] ?? null,
            'status' => 'confirmed',
        ]);

        $this->sendConfirmation($booking->fresh(['service', 'vehicle', 'user']));

        return $booking;
    }

    public function sendConfirmation(Booking $booking): void
    {
        if (! $booking->customer_email || $booking->confirmation_sent_at) {
            return;
        }

        Mail::to($booking->customer_email)->send(new AppointmentScheduledMail($booking));

        $booking->update(['confirmation_sent_at' => now()]);
    }

    public function sendReminder(Booking $booking): void
    {
        if (! $booking->customer_email || $booking->reminder_sent_at) {
            return;
        }

        Mail::to($booking->customer_email)->send(new AppointmentReminderMail($booking));

        $booking->update(['reminder_sent_at' => now()]);
    }

    public function buildRemindersForUser(User $user): array
    {
        $bookings = $user->bookings()
            ->with(['service', 'vehicle'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('scheduled_date', '>=', now()->toDateString())
            ->orderBy('scheduled_date')
            ->get();

        return $bookings->map(function (Booking $booking) {
            $daysUntil = (int) now()->startOfDay()->diffInDays($booking->scheduled_date->copy()->startOfDay());
            $showPopup = $daysUntil <= 7 && ! $booking->popup_dismissed_at;

            return [
                'id' => 'booking-'.$booking->id,
                'type' => 'appointment',
                'booking_id' => $booking->id,
                'title' => $daysUntil === 0 ? 'Appointment Today' : ($daysUntil === 1 ? 'Appointment Tomorrow' : 'Upcoming Appointment'),
                'message' => $this->reminderMessage($booking, $daysUntil),
                'service_name' => $booking->service?->name,
                'scheduled_date' => $booking->scheduled_date->toDateString(),
                'scheduled_time' => substr((string) $booking->scheduled_time, 0, 5),
                'vehicle' => $booking->vehicle
                    ? trim("{$booking->vehicle->year} {$booking->vehicle->make} {$booking->vehicle->model}")
                    : null,
                'status' => $booking->status,
                'days_until' => max(0, $daysUntil),
                'show_popup' => $showPopup,
                'scheduled_by_staff' => (bool) $booking->scheduled_by_user_id,
            ];
        })->values()->all();
    }

    public function dismissPopup(Booking $booking): void
    {
        $booking->update(['popup_dismissed_at' => now()]);
    }

    private function reminderMessage(Booking $booking, int $daysUntil): string
    {
        $service = $booking->service?->name ?? 'service';
        $time = substr((string) $booking->scheduled_time, 0, 5);

        return match (true) {
            $daysUntil === 0 => "Your {$service} appointment is today at {$time}. We look forward to seeing you!",
            $daysUntil === 1 => "Reminder: your {$service} appointment is tomorrow at {$time}.",
            default => "Your next {$service} appointment is in {$daysUntil} days on {$booking->scheduled_date->format('M j')} at {$time}.",
        };
    }
}
