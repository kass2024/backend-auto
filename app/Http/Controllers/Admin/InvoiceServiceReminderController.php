<?php

namespace App\Http\Controllers\Admin;

use App\Filament\Resources\InvoiceResource;
use App\Filament\Support\AdminFlashNotifications;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoiceServiceReminderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class InvoiceServiceReminderController extends Controller
{
    public function show(Invoice $invoice): RedirectResponse
    {
        return redirect()->to(InvoiceResource::getUrl('index'));
    }

    public function store(Request $request, Invoice $invoice, InvoiceServiceReminderService $reminders): RedirectResponse
    {
        $validated = $request->validate([
            'schedule_mode' => 'required|in:datetime,offset',
            'next_service_at' => 'required_if:schedule_mode,datetime|nullable|date|after:now',
            'offset_amount' => 'required_if:schedule_mode,offset|nullable|integer|min:1|max:3650',
            'reminder_unit' => 'required|in:minutes,hours,days',
            'next_service_repeat' => 'required|in:none,weekly,biweekly,monthly,quarterly,yearly',
            'next_service_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $serviceAt = $this->resolveServiceAt($validated);
            $reminders->schedule(
                $invoice,
                $serviceAt,
                $validated['reminder_unit'],
                $validated['next_service_notes'] ?? null,
                $validated['next_service_repeat'],
            );

            $repeatNote = $validated['next_service_repeat'] !== 'none'
                ? ' Repeats: '.InvoiceServiceReminderService::repeatLabel($validated['next_service_repeat']).'.'
                : '';

            AdminFlashNotifications::flash(
                'success',
                'Service reminder scheduled',
                'Customer will be reminded for invoice '.$invoice->invoice_number.' on '.$serviceAt->format('M j, Y g:i A').'.'.$repeatNote,
            );
        } catch (\Throwable $e) {
            AdminFlashNotifications::flash('danger', 'Could not schedule reminder', $e->getMessage());

            return redirect()->back()->withInput();
        }

        return redirect()->to(InvoiceResource::getUrl('index'));
    }

    public function sendNow(Invoice $invoice, InvoiceServiceReminderService $reminders): RedirectResponse
    {
        try {
            $reminders->sendManualReminder($invoice);

            AdminFlashNotifications::flash(
                'success',
                'Reminder sent',
                'Email and dashboard notification sent to '.$invoice->user?->email.' for invoice '.$invoice->invoice_number.'.',
            );
        } catch (\Throwable $e) {
            AdminFlashNotifications::flash('danger', 'Could not send reminder', $e->getMessage());
        }

        return redirect()->to(InvoiceResource::getUrl('index'));
    }

    public function destroy(Invoice $invoice, InvoiceServiceReminderService $reminders): RedirectResponse
    {
        $reminders->clear($invoice);

        AdminFlashNotifications::flash(
            'success',
            'Service reminder cleared',
            'Reminders for invoice '.$invoice->invoice_number.' have been removed.',
        );

        return redirect()->to(InvoiceResource::getUrl('index'));
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function resolveServiceAt(array $validated): Carbon
    {
        if ($validated['schedule_mode'] === 'offset') {
            $amount = (int) $validated['offset_amount'];

            return match ($validated['reminder_unit']) {
                InvoiceServiceReminderService::UNIT_MINUTES => now()->addMinutes($amount),
                InvoiceServiceReminderService::UNIT_HOURS => now()->addHours($amount),
                InvoiceServiceReminderService::UNIT_DAYS => now()->addDays($amount),
                default => throw new \InvalidArgumentException('Invalid reminder unit.'),
            };
        }

        return Carbon::parse($validated['next_service_at']);
    }
}
