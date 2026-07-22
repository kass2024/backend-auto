<?php

namespace App\Filament\Resources\QuotationResource\Pages;

use App\Filament\Resources\QuotationResource;
use App\Models\Quotation;
use App\Services\QuotationService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditQuotation extends EditRecord
{
    protected static string $resource = QuotationResource::class;

    protected array $lineData = [];

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if (in_array($this->record->status, ['accepted', 'converted'], true)) {
            $this->redirect(QuotationResource::getUrl('view', ['record' => $this->record]));
        }
    }
    protected function mutateFormDataBeforeFill(array $data): array
    {
        return array_merge($data, app(QuotationService::class)->formLineData($this->record));
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->lineData = [
            'part_lines' => $data['part_lines'] ?? [],
            'labor_lines' => $data['labor_lines'] ?? [],
            'additional_lines' => $data['additional_lines'] ?? [],
        ];

        unset($data['part_lines'], $data['labor_lines'], $data['additional_lines'], $data['send_to_customer']);

        return $data;
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        return app(QuotationService::class)->updateFromForm(
            $record,
            $data,
            $this->lineData['part_lines'] ?? [],
            $this->lineData['labor_lines'] ?? [],
            $this->lineData['additional_lines'] ?? [],
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn (Quotation $record): bool => $record->status !== 'converted'),
        ];
    }

    protected function getRedirectUrl(): ?string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Smart quote updated')
            ->body('Quote '.$this->record->quote_number.' has been saved.');
    }
}
