<?php

namespace App\Filament\Resources\JobCardResource\Pages;

use App\Filament\Resources\JobCardResource;
use App\Models\Mechanic;
use App\Models\Service;
use App\Services\AppointmentService;
use App\Services\JobCardWorkflowService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditJobCard extends EditRecord
{
    protected static string $resource = JobCardResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['line_items'] = $this->record->lines()
            ->orderBy('id')
            ->get()
            ->map(fn ($line) => [
                'service_id' => $line->service_id,
                'description' => $line->description,
                'quantity' => $line->quantity,
                'unit_price' => $line->unit_price,
            ])
            ->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['line_items']);

        return $data;
    }

    protected function afterSave(): void
    {
        $lines = $this->form->getState()['line_items'] ?? [];

        app(JobCardWorkflowService::class)->syncLinesFromForm($this->record, $lines);

        $invoice = app(JobCardWorkflowService::class)->autoInvoiceIfReady(
            $this->record->fresh(['lines', 'user', 'invoice', 'service'])
        );

        if ($invoice) {
            Notification::make()
                ->title('Invoice '.$invoice->invoice_number.' generated automatically')
                ->success()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('scheduleNextAppointment')
                ->label('Schedule Next Appointment')
                ->icon('heroicon-o-calendar-days')
                ->color('success')
                ->visible(fn () => in_array($this->record->status, ['ready_for_pickup', 'delivered', 'in_progress', 'quality_check'], true))
                ->form([
                    Forms\Components\Select::make('service_id')
                        ->label('Service')
                        ->options(fn () => Service::query()->where('is_active', true)->orderBy('sort_order')->pluck('name', 'id'))
                        ->default(fn () => $this->record->service_id)
                        ->required()
                        ->searchable(),
                    Forms\Components\DatePicker::make('scheduled_date')
                        ->required()
                        ->minDate(now())
                        ->default(now()->addDays(14)),
                    Forms\Components\TimePicker::make('scheduled_time')
                        ->required()
                        ->default('09:00'),
                    Forms\Components\Select::make('mechanic_id')
                        ->label('Mechanic')
                        ->options(fn () => Mechanic::query()->orderBy('name')->pluck('name', 'id'))
                        ->default(fn () => $this->record->mechanic_id)
                        ->searchable(),
                    Forms\Components\Textarea::make('staff_notes')
                        ->label('Note for customer')
                        ->placeholder('e.g. Recommended oil change in 3 months')
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    $booking = app(AppointmentService::class)->scheduleFromJobCard(
                        $this->record->fresh(['user', 'vehicle', 'service']),
                        $data,
                        auth()->user()
                    );

                    Notification::make()
                        ->title('Appointment '.$booking->reference.' scheduled')
                        ->body('Confirmation email sent to '.$booking->customer_email)
                        ->success()
                        ->send();
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
