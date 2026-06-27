<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Filament\Support\InvoiceFlashNotifications;
use App\Filament\Support\InvoiceFormSchema;
use App\Services\InvoiceService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected bool $shouldEmailCustomer = true;

    protected array $lineData = [];

    protected ?string $createdNotificationTitle = null;

    protected ?string $createdNotificationBody = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['invoice_number'])) {
            $data['invoice_number'] = app(InvoiceService::class)->generateNumber();
        }

        $this->shouldEmailCustomer = (bool) ($data['send_to_customer'] ?? true);
        $this->lineData = [
            'part_lines' => $data['part_lines'] ?? [],
            'service_lines' => $data['service_lines'] ?? [],
        ];

        unset($data['send_to_customer'], $data['part_lines'], $data['service_lines']);

        return InvoiceFormSchema::applyComputedTotals($data);
    }

    protected function afterCreate(): void
    {
        $invoiceService = app(InvoiceService::class);

        $invoiceService->syncLineItems(
            $this->record,
            $this->lineData['part_lines'] ?? [],
            $this->lineData['service_lines'] ?? [],
        );
        $invoiceService->recalculateTotals($this->record);

        if ($this->shouldEmailCustomer && $this->record->status !== 'draft') {
            try {
                $invoiceService->sendToCustomer($this->record->fresh());
            } catch (\Throwable $e) {
                $this->createdNotificationTitle = 'Invoice saved — email not sent';
                $this->createdNotificationBody = $e->getMessage();

                InvoiceFlashNotifications::flash('danger', $this->createdNotificationTitle, $this->createdNotificationBody);

                return;
            }

            $sent = $this->record->fresh();
            $stripeNote = $sent->wantsStripePayment() && ! $sent->isPaid()
                ? ' Stripe payment link included.'
                : ' No Stripe link'.($sent->paymentMethodLabel() ? ' (payment method: '.$sent->paymentMethodLabel().')' : '').'.';

            $this->createdNotificationTitle = 'Invoice created and emailed successfully';
            $this->createdNotificationBody = 'Invoice '.$sent->invoice_number.' was sent to '.$sent->user?->email.'.'.$stripeNote.' Check inbox and spam folder.';

            InvoiceFlashNotifications::flash('success', $this->createdNotificationTitle, $this->createdNotificationBody);

            return;
        }

        $this->createdNotificationTitle = 'Invoice created';
        $this->createdNotificationBody = 'Invoice '.$this->record->invoice_number.' saved successfully.';

        InvoiceFlashNotifications::flash('success', $this->createdNotificationTitle, $this->createdNotificationBody);
    }

    protected function getCreatedNotification(): ?Notification
    {
        if (! $this->createdNotificationTitle) {
            return null;
        }

        $notification = Notification::make()
            ->title($this->createdNotificationTitle)
            ->body($this->createdNotificationBody ?? '')
            ->duration(12000);

        if (str_contains($this->createdNotificationTitle, 'not sent')) {
            return $notification->danger();
        }

        return $notification->success();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
