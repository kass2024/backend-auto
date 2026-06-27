<?php

namespace App\Filament\Support;

use App\Models\Vehicle;
use Filament\Forms\Get;
use Filament\Forms\Set;

class CustomerVehicleAutofill
{
    public static function vehicleOptions(?int $userId): array
    {
        if (! $userId) {
            return [];
        }

        return Vehicle::query()
            ->where('user_id', $userId)
            ->orderBy('plate_number')
            ->get()
            ->mapWithKeys(fn (Vehicle $vehicle) => [
                (string) $vehicle->id => self::vehicleLabel($vehicle),
            ])
            ->all();
    }

    public static function vehicleLabel(Vehicle $vehicle): string
    {
        $summary = trim(implode(' ', array_filter([
            $vehicle->year,
            $vehicle->make,
            $vehicle->model,
        ])));

        return $summary !== ''
            ? "{$vehicle->plate_number} — {$summary}"
            : $vehicle->plate_number;
    }

    public static function applyCustomer(Set $set, mixed $userId): void
    {
        $set('vehicle_id', null);
        $set('odometer', null);

        if (! filled($userId)) {
            return;
        }

        $userId = (int) $userId;

        $vehicle = Vehicle::query()
            ->where('user_id', $userId)
            ->orderBy('plate_number')
            ->first();

        if (! $vehicle) {
            return;
        }

        $set('vehicle_id', $vehicle->id);
        self::applyVehicle($set, $vehicle->id);
    }

    public static function applyVehicle(Set $set, mixed $vehicleId): void
    {
        if (! filled($vehicleId)) {
            $set('odometer', null);

            return;
        }

        $vehicle = Vehicle::find($vehicleId);

        if (! $vehicle) {
            return;
        }

        if (filled($vehicle->mileage)) {
            $set('odometer', $vehicle->mileage);
        }
    }

    public static function vehicleSummary(?int $vehicleId): string
    {
        if (! $vehicleId) {
            return '—';
        }

        $vehicle = Vehicle::find($vehicleId);

        if (! $vehicle) {
            return '—';
        }

        return trim(implode(' · ', array_filter([
            $vehicle->plate_number,
            $vehicle->year,
            $vehicle->make,
            $vehicle->model,
            $vehicle->color ? "Color: {$vehicle->color}" : null,
            $vehicle->mileage ? number_format($vehicle->mileage).' mi' : null,
        ])));
    }

    public static function vehicleCount(Get $get): int
    {
        $userId = $get('user_id');

        if (! filled($userId)) {
            return 0;
        }

        return Vehicle::query()->where('user_id', (int) $userId)->count();
    }
}
