<?php

namespace App\Filament\Support;

use App\Models\User;
use App\Models\Vehicle;

class CustomerVehicleSync
{
    /**
     * @param  array<int, array<string, mixed>>  $vehicles
     */
    public static function sync(User $customer, array $vehicles): void
    {
        $keptIds = [];

        foreach ($vehicles as $vehicleData) {
            if (! filled($vehicleData['plate_number'] ?? null)) {
                continue;
            }

            $attrs = [
                'plate_number' => self::normalizePlate($vehicleData['plate_number']),
                'make' => $vehicleData['make'] ?? '',
                'model' => $vehicleData['model'] ?? '',
                'year' => $vehicleData['year'] ?? null,
                'color' => $vehicleData['color'] ?? null,
                'mileage' => $vehicleData['mileage'] ?? null,
                'notes' => $vehicleData['notes'] ?? null,
                'user_id' => $customer->id,
                'vin' => null,
            ];

            $vehicleId = filled($vehicleData['id'] ?? null) ? (int) $vehicleData['id'] : null;

            if ($vehicleId) {
                $vehicle = Vehicle::query()
                    ->where('id', $vehicleId)
                    ->where('user_id', $customer->id)
                    ->first();

                if ($vehicle) {
                    $vehicle->update($attrs);
                    $keptIds[] = $vehicle->id;

                    continue;
                }
            }

            $vehicle = $customer->vehicles()->create($attrs);
            $keptIds[] = $vehicle->id;
        }

        $deleteQuery = Vehicle::query()->where('user_id', $customer->id);

        if ($keptIds !== []) {
            $deleteQuery->whereNotIn('id', $keptIds);
        }

        $deleteQuery->delete();
    }

    public static function normalizePlate(string $plate): string
    {
        return strtoupper(trim($plate));
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
}
