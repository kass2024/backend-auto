<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\InvoiceServiceReminderService;
use Illuminate\Console\Command;

class ListInvoiceServiceReminders extends Command
{
    protected $signature = 'invoices:list-service-reminders';

    protected $description = 'List invoices that have an active next-service reminder saved';

    public function handle(): int
    {
        $invoices = Invoice::query()
            ->whereNotNull('next_service_at')
            ->whereNotNull('next_service_reminder_unit')
            ->with(['user:id,name,email'])
            ->orderBy('next_service_at')
            ->get();

        if ($invoices->isEmpty()) {
            $this->info('No service reminders are saved on any invoice.');
            $this->line('');
            $this->line('Note: `php artisan schedule:list` only shows the cron tasks that check for reminders — not reminders you created.');

            return self::SUCCESS;
        }

        $this->table(
            ['Invoice', 'Customer', 'Service at (local)', 'Time zone', 'Alert', 'Repeat'],
            $invoices->map(fn (Invoice $invoice) => [
                $invoice->invoice_number,
                $invoice->user?->email ?? '—',
                $invoice->nextServiceAtLocal()?->format('Y-m-d H:i'),
                InvoiceServiceReminderService::timezoneLabel($invoice->serviceReminderTimezone()),
                $invoice->next_service_reminder_unit,
                InvoiceServiceReminderService::repeatLabel($invoice->next_service_repeat),
            ])
        );

        $this->line("Total: {$invoices->count()} active reminder(s).");

        return self::SUCCESS;
    }
}
