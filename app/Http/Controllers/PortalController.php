<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\JobCard;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();

        return view('portal.dashboard', [
            'vehicles' => $user->vehicles()->latest()->get(),
            'bookings' => $user->bookings()->with(['service', 'vehicle'])->latest()->take(5)->get(),
            'jobCards' => $user->jobCards()->with(['vehicle'])->latest()->take(5)->get(),
            'invoices' => $user->invoices()->latest()->take(5)->get(),
            'loyaltyPoints' => $user->loyalty_points,
        ]);
    }

    public function vehicles()
    {
        return view('portal.vehicles', [
            'vehicles' => auth()->user()->vehicles()->withCount('jobCards')->latest()->get(),
        ]);
    }

    public function storeVehicle(Request $request)
    {
        $validated = $request->validate([
            'plate_number' => 'required|string|max:20',
            'make' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'vin' => 'nullable|string|max:17',
            'mileage' => 'nullable|integer|min:0',
            'color' => 'nullable|string|max:50',
        ]);

        auth()->user()->vehicles()->create($validated);

        return back()->with('success', 'Vehicle added successfully.');
    }

    public function bookings()
    {
        return view('portal.bookings', [
            'bookings' => auth()->user()->bookings()->with(['service', 'vehicle'])->latest()->paginate(10),
        ]);
    }

    public function cancelBooking(Booking $booking)
    {
        abort_unless($booking->user_id === auth()->id(), 403);
        abort_unless(in_array($booking->status, ['pending', 'confirmed']), 400);

        $booking->update(['status' => 'cancelled']);

        return back()->with('success', 'Booking cancelled successfully.');
    }

    public function tracking()
    {
        return view('portal.tracking', [
            'jobCards' => auth()->user()->jobCards()->with(['vehicle'])->latest()->get(),
        ]);
    }

    public function invoices()
    {
        return view('portal.invoices', [
            'invoices' => auth()->user()->invoices()->latest()->paginate(10),
        ]);
    }
}
