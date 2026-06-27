<?php

namespace App\Services;

use App\Mail\InvoiceServiceReminderMail;
use App\Models\CustomerNotification;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InvoiceServiceReminderService
{
    public const UNIT_MINUTES = 'minutes';

    public const UNIT_HOURS = 'hours';

    public const UNIT_DAYS = 'days';

    public const REPEAT_NONE = 'none';

    public const REPEAT_WEEKLY = 'weekly';

    public const REPEAT_BIWEEKLY = 'biweekly';

    public const REPEAT_MONTHLY = 'monthly';

    public const REPEAT_QUARTERLY = 'quarterly';

    public const REPEAT_YEARLY = 'yearly';

    /**
     * @return array<string, string>
     */
    public static function unitOptions(): array
    {
        return [
            self::UNIT_MINUTES => 'Minutes (remind 5 min before)',
            self::UNIT_HOURS => 'Hours (remind 1 hour before)',
            self::UNIT_DAYS => 'Days (remind 5 days before + on service date)',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function repeatOptions(): array
    {
        return [
            self::REPEAT_NONE => 'One time only',
            self::REPEAT_WEEKLY => 'Every week',
            self::REPEAT_BIWEEKLY => 'Every 2 weeks',
            self::REPEAT_MONTHLY => 'Every month',
            self::REPEAT_QUARTERLY => 'Every 3 months',
            self::REPEAT_YEARLY => 'Every year',
        ];
    }

    public static function repeatLabel(?string $repeat): string
    {
        return self::repeatOptions()[$repeat ?? self::REPEAT_NONE] ?? 'One time only';
    }

    public function schedule(
        Invoice $invoice,
        Carbon $serviceAt,
        string $unit,
        ?string $notes = null,
        string $repeat = self::REPEAT_NONE,
    ): void {
        if (! in_array($unit, [self::UNIT_MINUTES, self::UNIT_HOURS, self::UNIT_DAYS], true)) {
            throw new \InvalidArgumentException('Invalid reminder unit.');
        }

        if ($serviceAt->lte(now())) {
            throw new \RuntimeException('Next service date must be in the future.');
        }

        $validRepeats = array_keys(self::repeatOptions());
        if (! in_array($repeat, $validRepeats, true)) {
            throw new \InvalidArgumentException('Invalid repeat interval.');
        }

        $invoice->update([
            'next_service_at' => $serviceAt,
            'next_service_reminder_unit' => $unit,
            'next_service_repeat' => $repeat,
            'next_service_notes' => $notes,
            'next_service_reminder_early_sent_at' => null,
            'next_service_reminder_due_sent_at' => null,
            'next_service_popup_dismissed_at' => null,
        ]);
    }

    public function clear(Invoice $invoice): void
    {
        $invoice->update([
            'next_service_at' => null,
            'next_service_reminder_unit' => null,
            'next_service_repeat' => self::REPEAT_NONE,
            'next_service_notes' => null,
            'next_service_reminder_early_sent_at' => null,
            'next_service_reminder_due_sent_at' => null,
            'next_service_popup_dismissed_at' => null,
        ]);
    }

    public function sendManualReminder(Invoice $invoice): void
    {
        if (! $invoice->hasServiceReminder()) {
            throw new \RuntimeException('No service reminder is scheduled for this invoice.');
        }

        if (! $this->deliverReminder($invoice, 'manual', markScheduled: false)) {
            throw new \RuntimeException('Could not send reminder. Check customer email and SMTP settings.');
        }
    }

    public function earlyReminderAt(Invoice $invoice): ?Carbon
    {
        if (! $invoice->next_service_at || ! $invoice->next_service_reminder_unit) {
            return null;
        }

        return match ($invoice->next_service_reminder_unit) {
            self::UNIT_MINUTES => $invoice->next_service_at->copy()->subMinutes(5),
            self::UNIT_HOURS => $invoice->next_service_at->copy()->subHour(),
            self::UNIT_DAYS => $invoice->next_service_at->copy()->subDays(5),
            default => null,
        };
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function buildUpcomingRemindersForUser(User $user): array
    {
        return $user->invoices()
            ->with('vehicle')
            ->whereNotNull('next_service_at')
            ->whereNotNull('next_service_reminder_unit')
            ->where('next_service_at', '>', now())
            ->orderBy('next_service_at')
            ->get()
            ->map(fn (Invoice $invoice) => $this->mapInvoiceReminder($invoice))
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function buildNotificationFeedForUser(User $user): array
    {
        return CustomerNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('dismissed_at')
            ->with(['invoice.vehicle'])
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn (CustomerNotification $notification) => [
                'id' => 'notification-'.$notification->id,
                'notification_id' => $notification->id,
                'type' => 'service_notification',
                'kind' => $notification->kind,
                'title' => $notification->title,
                'message' => $notification->message,
                'invoice_id' => $notification->invoice_id,
                'invoice_number' => $notification->invoice?->invoice_number,
                'service_name' => $notification->invoice?->next_service_notes ?: 'Next service',
                'scheduled_date' => $notification->invoice?->next_service_at?->toDateString(),
                'scheduled_time' => $notification->invoice?->next_service_at?->format('H:i'),
                'vehicle' => $this->vehicleLabel($notification->invoice),
                'show_popup' => true,
                'created_at' => $notification->created_at?->toIso8601String(),
            ])
            ->all();
    }

    public function pickPopupReminder(User $user, array $appointmentReminders): ?array
    {
        $notification = collect($this->buildNotificationFeedForUser($user))->first();

        if ($notification) {
            return $notification;
        }

        $servicePopup = collect($this->buildUpcomingRemindersForUser($user))
            ->first(fn (array $reminder) => $reminder['show_popup'] ?? false);

        if ($servicePopup) {
            return $servicePopup;
        }

        return collect($appointmentReminders)->firstWhere('show_popup', true);
    }

    public function dismissPopup(Invoice $invoice): void
    {
        $invoice->update(['next_service_popup_dismissed_at' => now()]);
    }

    public function dismissNotification(CustomerNotification $notification): void
    {
        $notification->update(['dismissed_at' => now()]);
    }

    public function sendDueReminders(): int
    {
        $count = 0;

        Invoice::query()
            ->whereNotNull('next_service_at')
            ->whereNotNull('next_service_reminder_unit')
            ->where('next_service_at', '>', now()->subDay())
            ->with(['user', 'vehicle'])
            ->orderBy('id')
            ->chunkById(100, function ($invoices) use (&$count): void {
                foreach ($invoices as $invoice) {
                    if ($this->sendEarlyReminderIfDue($invoice)) {
                        $count++;
                    }

                    if ($this->sendDueReminderIfDue($invoice)) {
                        $count++;
                    }
                }
            });

        $count += $this->advanceRepeatingReminders();

        return $count;
    }

    public function advanceRepeatingReminders(): int
    {
        $advanced = 0;

        Invoice::query()
            ->whereNotNull('next_service_at')
            ->whereNotNull('next_service_reminder_unit')
            ->whereNotNull('next_service_repeat')
            ->where('next_service_repeat', '!=', self::REPEAT_NONE)
            ->where('next_service_at', '<', now())
            ->orderBy('id')
            ->chunkById(100, function ($invoices) use (&$advanced): void {
                foreach ($invoices as $invoice) {
                    if ($this->maybeAdvanceRepeat($invoice)) {
                        $advanced++;
                    }
                }
            });

        return $advanced;
    }

    public function maybeAdvanceRepeat(Invoice $invoice): bool
    {
        $repeat = $invoice->next_service_repeat;
        if (! $repeat || $repeat === self::REPEAT_NONE || ! $invoice->next_service_at) {
            return false;
        }

        if (now()->lte($invoice->next_service_at)) {
            return false;
        }

        if ($invoice->next_service_reminder_unit === self::UNIT_DAYS) {
            if (! $invoice->next_service_reminder_due_sent_at && now()->lt($invoice->next_service_at->copy()->addHour())) {
                return false;
            }
        } elseif (! $invoice->next_service_reminder_early_sent_at && now()->lt($invoice->next_service_at->copy()->addMinutes(30))) {
            return false;
        }

        $invoice->update([
            'next_service_at' => $this->nextRepeatOccurrence($invoice->next_service_at, $repeat),
            'next_service_reminder_early_sent_at' => null,
            'next_service_reminder_due_sent_at' => null,
            'next_service_popup_dismissed_at' => null,
        ]);

        Log::info('Service reminder repeated', [
            'invoice_id' => $invoice->id,
            'repeat' => $repeat,
            'next_service_at' => $invoice->fresh()->next_service_at,
        ]);

        return true;
    }

    public function nextRepeatOccurrence(Carbon $from, string $repeat): Carbon
    {
        $next = $from->copy();

        do {
            $next = match ($repeat) {
                self::REPEAT_WEEKLY => $next->addWeek(),
                self::REPEAT_BIWEEKLY => $next->addWeeks(2),
                self::REPEAT_MONTHLY => $next->addMonth(),
                self::REPEAT_QUARTERLY => $next->addMonths(3),
                self::REPEAT_YEARLY => $next->addYear(),
                default => $next->addMonth(),
            };
        } while ($next->lte(now()));

        return $next;
    }

    public function sendEarlyReminderIfDue(Invoice $invoice): bool
    {
        if ($invoice->next_service_reminder_early_sent_at) {
            return false;
        }

        $earlyAt = $this->earlyReminderAt($invoice);

        if (! $earlyAt || now()->lt($earlyAt)) {
            return false;
        }

        if (now()->gte($invoice->next_service_at)) {
            return false;
        }

        return $this->deliverReminder($invoice, 'early');
    }

    public function sendDueReminderIfDue(Invoice $invoice): bool
    {
        if ($invoice->next_service_reminder_unit !== self::UNIT_DAYS) {
            return false;
        }

        if ($invoice->next_service_reminder_due_sent_at) {
            return false;
        }

        if (now()->lt($invoice->next_service_at)) {
            return false;
        }

        return $this->deliverReminder($invoice, 'due');
    }

    /**
     * @return array<string, mixed>
     */
    private function mapInvoiceReminder(Invoice $invoice): array
    {
        $serviceAt = $invoice->next_service_at;
        $minutesUntil = (int) now()->diffInMinutes($serviceAt, false);
        $daysUntil = (int) now()->startOfDay()->diffInDays($serviceAt->copy()->startOfDay(), false);
        $inPopupWindow = $this->isInPopupWindow($invoice);
        $showPopup = $inPopupWindow && ! $invoice->next_service_popup_dismissed_at;

        return [
            'id' => 'service-'.$invoice->id,
            'type' => 'service_reminder',
            'invoice_id' => $invoice->id,
            'title' => match (true) {
                $daysUntil === 0 => 'Next Service Today',
                $daysUntil === 1 => 'Next Service Tomorrow',
                default => 'Upcoming Service Reminder',
            },
            'message' => $this->reminderMessage($invoice, $daysUntil, $minutesUntil),
            'service_name' => $invoice->next_service_notes ?: 'Scheduled service',
            'scheduled_date' => $serviceAt->toDateString(),
            'scheduled_time' => $serviceAt->format('H:i'),
            'vehicle' => $this->vehicleLabel($invoice),
            'invoice_number' => $invoice->invoice_number,
            'days_until' => max(0, $daysUntil),
            'minutes_until' => max(0, $minutesUntil),
            'show_popup' => $showPopup,
            'reminder_unit' => $invoice->next_service_reminder_unit,
            'repeat' => $invoice->next_service_repeat ?? self::REPEAT_NONE,
            'repeat_label' => self::repeatLabel($invoice->next_service_repeat),
        ];
    }

    private function isInPopupWindow(Invoice $invoice): bool
    {
        if (! $invoice->next_service_at || ! $invoice->next_service_reminder_unit) {
            return false;
        }

        $now = now();
        $serviceAt = $invoice->next_service_at;

        return match ($invoice->next_service_reminder_unit) {
            self::UNIT_MINUTES => $now->between($serviceAt->copy()->subMinutes(5), $serviceAt),
            self::UNIT_HOURS => $now->between($serviceAt->copy()->subHour(), $serviceAt),
            self::UNIT_DAYS => $now->gte($serviceAt->copy()->subDays(5)) && $now->lte($serviceAt->copy()->endOfDay()),
            default => false,
        };
    }

    private function reminderMessage(Invoice $invoice, int $daysUntil, int $minutesUntil): string
    {
        $time = $invoice->next_service_at?->format('g:i A') ?? '';
        $label = $invoice->next_service_notes ?: 'service';

        return match (true) {
            $daysUntil === 0 => "Your {$label} is scheduled today at {$time}.",
            $daysUntil === 1 => "Reminder: your {$label} is tomorrow at {$time}.",
            $invoice->next_service_reminder_unit === self::UNIT_MINUTES && $minutesUntil <= 5 => "Your {$label} starts in about {$minutesUntil} minute(s) at {$time}.",
            $invoice->next_service_reminder_unit === self::UNIT_HOURS && $minutesUntil <= 60 => "Your {$label} is in about an hour at {$time}.",
            default => "Your next {$label} is on {$invoice->next_service_at->format('M j')} at {$time}.",
        };
    }

    private function vehicleLabel(?Invoice $invoice): ?string
    {
        if (! $invoice?->vehicle) {
            return null;
        }

        return trim("{$invoice->vehicle->year} {$invoice->vehicle->make} {$invoice->vehicle->model}");
    }

    private function deliverReminder(Invoice $invoice, string $type, bool $markScheduled = true): bool
    {
        $invoice->loadMissing(['user', 'vehicle']);

        $email = $invoice->user?->email;

        if (! $email) {
            Log::warning('Service reminder skipped — no customer email', [
                'invoice_id' => $invoice->id,
                'type' => $type,
            ]);

            return false;
        }

        try {
            Mail::to($email)->send(new InvoiceServiceReminderMail($invoice, $type === 'manual' ? 'early' : $type));
        } catch (\Throwable $e) {
            Log::error('Service reminder email failed', [
                'invoice_id' => $invoice->id,
                'type' => $type,
                'recipient' => $email,
                'message' => $e->getMessage(),
            ]);

            return false;
        }

        $this->createDashboardNotification($invoice, $type);

        if ($markScheduled && $type !== 'manual') {
            $column = $type === 'due'
                ? 'next_service_reminder_due_sent_at'
                : 'next_service_reminder_early_sent_at';

            $invoice->update([$column => now()]);
        }

        Log::info('Service reminder sent', [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'type' => $type,
            'recipient' => $email,
        ]);

        return true;
    }

    private function createDashboardNotification(Invoice $invoice, string $kind): void
    {
        if (! $invoice->user_id) {
            return;
        }

        $serviceAt = $invoice->next_service_at;
        $timeLabel = $serviceAt?->format('M j, Y g:i A') ?? 'soon';

        [$title, $message] = match ($kind) {
            'due' => [
                'Service day reminder',
                "Your next service for invoice {$invoice->invoice_number} is today ({$timeLabel}).",
            ],
            'manual' => [
                'Service reminder from NEAMEE',
                "We've sent you a reminder for your upcoming service on {$timeLabel} (invoice {$invoice->invoice_number}).",
            ],
            default => [
                'Upcoming service reminder',
                "Reminder: your next service is scheduled for {$timeLabel} (invoice {$invoice->invoice_number}).",
            ],
        };

        CustomerNotification::create([
            'user_id' => $invoice->user_id,
            'invoice_id' => $invoice->id,
            'type' => 'service_reminder',
            'kind' => $kind,
            'title' => $title,
            'message' => $message,
        ]);
    }
}
