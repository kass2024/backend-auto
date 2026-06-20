<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\JobCard;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();

        $activeJob = $user->jobCards()
            ->with(['vehicle', 'mechanic'])
            ->whereNotIn('status', ['delivered'])
            ->latest()
            ->first();

        $upcomingBooking = $user->bookings()
            ->with(['service', 'vehicle'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('scheduled_date', '>=', now()->toDateString())
            ->orderBy('scheduled_date')
            ->first();

        return response()->json([
            'loyalty_points' => $user->loyalty_points,
            'loyalty_tier' => $this->loyaltyTier($user->loyalty_points),
            'vehicles_count' => $user->vehicles()->count(),
            'bookings_count' => $user->bookings()->count(),
            'active_repairs' => $user->jobCards()->whereNotIn('status', ['delivered'])->count(),
            'invoices_count' => $user->invoices()->count(),
            'pending_invoices' => $user->invoices()->whereIn('status', ['sent', 'overdue'])->count(),
            'total_spent' => (float) $user->invoices()->where('status', 'paid')->sum('total'),
            'active_job' => $activeJob ? [
                'id' => $activeJob->id,
                'job_number' => $activeJob->job_number,
                'status' => $activeJob->status,
                'status_label' => JobCard::statusLabel($activeJob->status),
                'vehicle' => $activeJob->vehicle,
                'mechanic' => $activeJob->mechanic?->name,
                'total_cost' => $activeJob->total_cost,
            ] : null,
            'upcoming_booking' => $upcomingBooking,
            'recent_bookings' => $user->bookings()->with(['service', 'vehicle'])->latest()->take(5)->get(),
            'recent_job_cards' => $user->jobCards()->with('vehicle')->latest()->take(5)->get(),
            'recent_invoices' => $user->invoices()->latest()->take(3)->get(),
        ]);
    }

    public function vehicles(Request $request)
    {
        return response()->json([
            'vehicles' => $request->user()->vehicles()->withCount('jobCards')->latest()->get(),
        ]);
    }

    public function storeVehicle(Request $request)
    {
        $validated = $request->validate([
            'plate_number' => 'required|string|max:20',
            'make' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'year' => 'nullable|integer|min:1900|max:'.(date('Y') + 1),
            'vin' => 'nullable|string|max:17',
            'mileage' => 'nullable|integer|min:0',
            'color' => 'nullable|string|max:50',
        ]);

        $vehicle = $request->user()->vehicles()->create($validated);

        return response()->json(['vehicle' => $vehicle, 'message' => 'Vehicle added.'], 201);
    }

    public function bookings(Request $request)
    {
        return response()->json([
            'bookings' => $request->user()->bookings()->with(['service', 'vehicle'])->latest()->paginate(10),
        ]);
    }

    public function cancelBooking(Request $request, Booking $booking)
    {
        abort_unless($booking->user_id === $request->user()->id, 403);
        abort_unless(in_array($booking->status, ['pending', 'confirmed']), 400);

        $booking->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Booking cancelled.', 'booking' => $booking]);
    }

    public function tracking(Request $request)
    {
        $jobCards = $request->user()->jobCards()->with('vehicle')->latest()->get();

        return response()->json([
            'job_cards' => $jobCards->map(fn (JobCard $job) => [
                'id' => $job->id,
                'job_number' => $job->job_number,
                'status' => $job->status,
                'status_label' => JobCard::statusLabel($job->status),
                'vehicle' => $job->vehicle,
                'total_cost' => $job->total_cost,
                'created_at' => $job->created_at,
            ]),
        ]);
    }

    public function invoices(Request $request)
    {
        return response()->json([
            'invoices' => $request->user()->invoices()->latest()->paginate(10),
        ]);
    }

    private function loyaltyTier(int $points): string
    {
        return match (true) {
            $points >= 500 => 'Gold',
            $points >= 200 => 'Silver',
            default => 'Bronze',
        };
    }
}
