@php
    use App\Filament\Resources\UserResource;
    use App\Filament\Resources\VehicleResource;

    /** @var \App\Models\Vehicle $record */
    $record = $getRecord();
    $canDelete = VehicleResource::canDelete($record);
@endphp

<div class="neamee-invoice-actions">
    @if (filled($record->user_id))
        <a
            href="{{ UserResource::getUrl('edit', ['record' => $record->user_id]) }}"
            class="neamee-invoice-action neamee-invoice-action--muted"
        >Customer</a>
    @endif

    @if ($canDelete)
        <form
            method="post"
            action="{{ route('filament.admin.vehicles.destroy', $record) }}"
            class="neamee-invoice-action-form"
            onsubmit="return confirm('Delete this vehicle? This cannot be undone.')"
        >
            @csrf
            @method('DELETE')
            <button type="submit" class="neamee-invoice-action neamee-invoice-action--danger">
                Delete
            </button>
        </form>
    @endif
</div>
