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

use Illuminate\Validation\Rule;



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

            'next_service_at' => 'required_if:schedule_mode,datetime|nullable|date_format:Y-m-d H:i:s',

            'offset_amount' => 'required_if:schedule_mode,offset|nullable|integer|min:1|max:3650',

            'reminder_unit' => 'required|in:minutes,hours,days',

            'next_service_repeat' => 'required|in:none,weekly,biweekly,monthly,quarterly,yearly',

            'next_service_timezone' => ['required', Rule::in(array_keys(InvoiceServiceReminderService::timezoneOptions()))],

            'next_service_notes' => 'nullable|string|max:1000',

        ]);



        try {

            $timezone = $validated['next_service_timezone'];

            $serviceAt = $this->resolveServiceAt($validated, $timezone);



            if ($serviceAt->lte(now())) {

                throw new \RuntimeException('Next service date must be in the future.');

            }



            $reminders->schedule(

                $invoice,

                $serviceAt,

                $validated['reminder_unit'],

                $validated['next_service_notes'] ?? null,

                $validated['next_service_repeat'],

                $timezone,

            );



            $local = $serviceAt->copy()->timezone($timezone);

            $repeatNote = $validated['next_service_repeat'] !== 'none'

                ? ' Repeats: '.InvoiceServiceReminderService::repeatLabel($validated['next_service_repeat']).'.'

                : '';



            AdminFlashNotifications::flash(

                'success',

                'Service reminder scheduled',

                'Customer will be reminded for invoice '.$invoice->invoice_number.' on '

                .$local->format('M j, Y g:i A').' ('.InvoiceServiceReminderService::timezoneLabel($timezone).').'.$repeatNote,

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

    private function resolveServiceAt(array $validated, string $timezone): Carbon

    {

        if ($validated['schedule_mode'] === 'offset') {

            $amount = (int) $validated['offset_amount'];

            $base = Carbon::now($timezone);



            return match ($validated['reminder_unit']) {

                InvoiceServiceReminderService::UNIT_MINUTES => $base->copy()->addMinutes($amount)->utc(),

                InvoiceServiceReminderService::UNIT_HOURS => $base->copy()->addHours($amount)->utc(),

                InvoiceServiceReminderService::UNIT_DAYS => $base->copy()->addDays($amount)->utc(),

                default => throw new \InvalidArgumentException('Invalid reminder unit.'),

            };

        }



        return InvoiceServiceReminderService::parseWallClock($validated['next_service_at'], $timezone);

    }

}

