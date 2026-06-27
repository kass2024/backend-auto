@php
    use App\Filament\Resources\InvoiceResource;

    /** @var \App\Models\Invoice $record */
    $record = $getRecord();
    $canEmail = in_array($record->status, ['draft', 'sent', 'overdue'], true);
    $wasEmailed = $record->wasEmailedToCustomer();
    $canMarkPaid = $record->status !== 'paid';
    $canMarkUnpaid = $record->status === 'paid';
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
            onsubmit="return confirm('{{ $wasEmailed ? 'Resend this invoice to the customer?' : 'Email this invoice to the customer?' }}')"
        >
            @csrf
            <button
                type="submit"
                class="neamee-invoice-action {{ $wasEmailed ? 'neamee-invoice-action--warning' : 'neamee-invoice-action--success' }}"
            >
                {{ $wasEmailed ? 'Resend' : 'Email customer' }}
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
