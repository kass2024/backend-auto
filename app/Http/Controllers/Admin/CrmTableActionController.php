<?php

namespace App\Http\Controllers\Admin;

use App\Filament\Resources\UserResource;
use App\Filament\Resources\VehicleResource;
use App\Filament\Support\AdminFlashNotifications;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;

class CrmTableActionController extends Controller
{
    public function destroyVehicle(Vehicle $vehicle): RedirectResponse
    {
        if (! VehicleResource::canDelete($vehicle)) {
            abort(403);
        }

        try {
            $label = $vehicle->plate_number ?: 'Vehicle';
            $vehicle->delete();

            AdminFlashNotifications::flash(
                'success',
                'Vehicle deleted successfully',
                $label.' has been removed.',
            );
        } catch (\Throwable $e) {
            AdminFlashNotifications::flash('danger', 'Delete failed', $e->getMessage());
        }

        return redirect()->to(VehicleResource::getUrl('index'));
    }

    public function destroyCustomer(User $customer): RedirectResponse
    {
        if ($customer->role !== 'customer') {
            abort(404);
        }

        if (! UserResource::canDelete($customer)) {
            abort(403);
        }

        try {
            $name = $customer->name ?: 'Customer';
            $customer->delete();

            AdminFlashNotifications::flash(
                'success',
                'Customer deleted successfully',
                $name.' has been removed.',
            );
        } catch (\Throwable $e) {
            AdminFlashNotifications::flash('danger', 'Delete failed', $e->getMessage());
        }

        return redirect()->to(UserResource::getUrl('index'));
    }
}
