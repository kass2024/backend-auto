@php
    use App\Filament\Resources\InvoiceResource;
    use App\Filament\Support\InvoiceEmailUi;

    /** @var \App\Models\Invoice $record */
    $record = $getRecord();
    $canEmail = in_array($record->status, ['draft', 'sent', 'overdue'], true);
    $wasEmailed = $record->wasEmailedToCustomer();
    $canMarkPaid = $record->status !== 'paid';
    $canMarkUnpaid = $record->status === 'paid';
    $customerEmail = $record->user?->email ?? 'the customer';
    $hasReminder = $record->hasServiceReminder();
@endphp

<div class="neamee-invoice-actions">
    <a
        href="{{ InvoiceResource::getUrl('view', ['record' => $record]) }}"
        class="neamee-invoice-action neamee-invoice-action--muted"
    >View</a>

    <a
        href="{{ route('filament.admin.invoices.print', $record) }}"
        target="_blank"
        rel="noopener"
        class="neamee-invoice-action neamee-invoice-action--muted"
    >Print</a>

    @if ($canEmail)
        <form
            method="post"
            action="{{ route('filament.admin.invoices.email', $record) }}"
            class="neamee-invoice-action-form"
            onsubmit="return confirm('{{ InvoiceEmailUi::confirmMessage($customerEmail, $wasEmailed) }}')"
        >
            @csrf
            <button
                type="submit"
                class="neamee-invoice-action {{ $wasEmailed ? 'neamee-invoice-action--warning' : 'neamee-invoice-action--success' }}"
            >
                {{ InvoiceEmailUi::actionLabel($wasEmailed) }}
            </button>
        </form>
    @endif

    @if ($canMarkPaid)
        <form
            method="post"
            action="{{ route('filament.admin.invoices.mark-paid', $record) }}"
            class="neamee-invoice-action-form"
            onsubmit="return confirm('Mark this invoice as paid?')"
        >
            @csrf
            <button type="submit" class="neamee-invoice-action neamee-invoice-action--primary">
                Mark paid
            </button>
        </form>
    @endif

    @if ($canMarkUnpaid)
        <form
            method="post"
            action="{{ route('filament.admin.invoices.mark-unpaid', $record) }}"
            class="neamee-invoice-action-form"
            onsubmit="return confirm('Mark this invoice as unpaid?')"
        >
            @csrf
            <button type="submit" class="neamee-invoice-action neamee-invoice-action--warning">
                Mark unpaid
            </button>
        </form>
    @endif

    <a
        href="{{ InvoiceResource::getUrl('edit', ['record' => $record]) }}"
        class="neamee-invoice-action neamee-invoice-action--muted"
    >Edit</a>

    <button
        type="button"
        class="neamee-invoice-action {{ $hasReminder ? 'neamee-invoice-action--warning' : 'neamee-invoice-action--info' }}"
        data-reminder-open
        data-invoice-number="{{ $record->invoice_number }}"
        data-customer-name="{{ $record->user?->name }}"
        data-customer-email="{{ $customerEmail }}"
        data-vehicle="{{ $record->vehicle?->plate_number }}"
        data-store-url="{{ route('filament.admin.invoices.service-reminder.store', $record) }}"
        data-send-url="{{ route('filament.admin.invoices.service-reminder.send-now', $record) }}"
        data-clear-url="{{ route('filament.admin.invoices.service-reminder.destroy', $record) }}"
        data-next-service-at="{{ $record->next_service_at?->format('Y-m-d H:i') }}"
        data-reminder-unit="{{ $record->next_service_reminder_unit ?? 'days' }}"
        data-repeat="{{ $record->next_service_repeat ?? 'none' }}"
        data-notes="{{ e($record->next_service_notes ?? '') }}"
        data-has-reminder="{{ $hasReminder ? '1' : '0' }}"
        title="{{ $hasReminder ? 'Reminder: '.$record->next_service_at?->format('M j, Y g:i A') : 'Schedule next service reminder' }}"
    >{{ $hasReminder ? 'Reminder' : 'Set reminder' }}</button>

    <form
        method="post"
        action="{{ route('filament.admin.invoices.destroy', $record) }}"
        class="neamee-invoice-action-form"
        onsubmit="return confirm('Delete this invoice? This cannot be undone.')"
    >
        @csrf
        @method('DELETE')
        <button type="submit" class="neamee-invoice-action neamee-invoice-action--danger">
            Delete
        </button>
    </form>
</div>
