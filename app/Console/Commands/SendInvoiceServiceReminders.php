<?php

namespace App\Console\Commands;

use App\Services\InvoiceServiceReminderService;
use Illuminate\Console\Command;

class SendInvoiceServiceReminders extends Command
{
    protected $signature = 'invoices:send-service-reminders';

    protected $description = 'Send next-service reminder emails for invoices (5 min / 1 hr / 5 days before, and on service day)';

    public function handle(InvoiceServiceReminderService $reminders): int
    {
        $count = $reminders->sendDueReminders();

        $this->info("Sent {$count} service reminder(s).");

        return self::SUCCESS;
    }
}
