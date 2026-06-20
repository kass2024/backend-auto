<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function services()
    {
        return response()->json([
            'services' => Service::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:30',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'scheduled_time' => 'required|date_format:H:i',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'vehicle_make' => 'nullable|string|max:100',
            'vehicle_model' => 'nullable|string|max:100',
            'vehicle_plate' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
        ]);

        $vehicleId = $validated['vehicle_id'] ?? null;

        if (! $vehicleId && $request->user() && ! empty($validated['vehicle_make'])) {
            $vehicle = Vehicle::create([
                'user_id' => $request->user()->id,
                'make' => $validated['vehicle_make'],
                'model' => $validated['vehicle_model'] ?? '',
                'plate_number' => $validated['vehicle_plate'] ?? 'TBD',
            ]);
            $vehicleId = $vehicle->id;
        }

        $booking = Booking::create([
            'reference' => 'BK-'.strtoupper(Str::random(8)),
            'user_id' => $request->user()?->id,
            'vehicle_id' => $vehicleId,
            'service_id' => $validated['service_id'],
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'],
            'customer_phone' => $validated['customer_phone'],
            'scheduled_date' => $validated['scheduled_date'],
            'scheduled_time' => $validated['scheduled_time'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Booking submitted successfully. We will confirm your appointment shortly.',
            'booking' => $booking->load('service'),
        ], 201);
    }
}
