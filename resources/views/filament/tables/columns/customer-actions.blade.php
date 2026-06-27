@php
    use App\Filament\Resources\UserResource;

    /** @var \App\Models\User $record */
    $record = $getRecord();
    $canDelete = UserResource::canDelete($record);
@endphp

<div class="neamee-invoice-actions">
    <a
        href="{{ UserResource::getUrl('view', ['record' => $record]) }}"
        class="neamee-invoice-action neamee-invoice-action--muted"
    >View</a>

    <a
        href="{{ UserResource::getUrl('edit', ['record' => $record]) }}"
        class="neamee-invoice-action neamee-invoice-action--muted"
    >Edit</a>

    @if ($canDelete)
        <form
            method="post"
            action="{{ route('filament.admin.customers.destroy', $record) }}"
            class="neamee-invoice-action-form"
            onsubmit="return confirm('Delete this customer? This cannot be undone.')"
        >
            @csrf
            @method('DELETE')
            <button type="submit" class="neamee-invoice-action neamee-invoice-action--danger">
                Delete
            </button>
        </form>
    @endif
</div>
