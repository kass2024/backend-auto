@extends('layouts.public')
@section('title', 'Customer Portal | NEAMEE Auto-Tech Solutions')
@section('content')
<section class="py-12 bg-black min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-10">
            <div>
                <h1 class="font-display text-3xl font-bold text-white">Welcome, {{ auth()->user()->name }}</h1>
                <p class="text-steel-400 text-sm">Customer Portal Dashboard</p>
            </div>
            <div class="flex items-center gap-2 px-4 py-2 bg-military-900/50 border border-military-700 rounded-lg">
                <span class="text-steel-400 text-sm">Loyalty Points:</span>
                <span class="text-military-400 font-bold">{{ number_format($loyaltyPoints) }}</span>
            </div>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
            @foreach([
                ['My Vehicles', $vehicles->count(), route('portal.vehicles'), 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4'],
                ['Bookings', auth()->user()->bookings()->count(), route('portal.bookings'), 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                ['Repairs in Progress', auth()->user()->jobCards()->whereNotIn('status', ['delivered'])->count(), route('portal.tracking'), 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                ['Invoices', auth()->user()->invoices()->count(), route('portal.invoices'), 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            ] as [$label, $count, $url, $icon])
            <a href="{{ $url }}" class="p-6 rounded-xl bg-steel-900 border border-steel-800 hover:border-military-600 transition group">
                <svg class="w-8 h-8 text-military-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/></svg>
                <p class="text-2xl font-bold text-white">{{ $count }}</p>
                <p class="text-steel-400 text-sm group-hover:text-military-400 transition">{{ $label }}</p>
            </a>
            @endforeach
        </div>

        <div class="grid lg:grid-cols-2 gap-8">
            <div class="bg-steel-900 border border-steel-800 rounded-2xl p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="font-display text-xl font-bold text-white">Recent Bookings</h2>
                    <a href="{{ route('portal.bookings') }}" class="text-military-400 text-sm hover:text-military-300">View all</a>
                </div>
                @forelse($bookings as $booking)
                <div class="py-3 border-b border-steel-800 last:border-0">
                    <p class="text-white font-medium">{{ $booking->service->name }}</p>
                    <p class="text-steel-500 text-sm">{{ $booking->scheduled_date->format('M d, Y') }} at {{ $booking->scheduled_time }} · <span class="capitalize">{{ $booking->status }}</span></p>
                </div>
                @empty
                <p class="text-steel-500 text-sm">No bookings yet. <a href="{{ route('booking.create') }}" class="text-military-400">Book a service</a></p>
                @endforelse
            </div>
            <div class="bg-steel-900 border border-steel-800 rounded-2xl p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="font-display text-xl font-bold text-white">Repair Tracking</h2>
                    <a href="{{ route('portal.tracking') }}" class="text-military-400 text-sm hover:text-military-300">View all</a>
                </div>
                @forelse($jobCards as $job)
                <div class="py-3 border-b border-steel-800 last:border-0">
                    <p class="text-white font-medium">{{ $job->vehicle->display_name ?? 'Vehicle' }}</p>
                    <p class="text-military-400 text-sm">{{ \App\Models\JobCard::statusLabel($job->status) }}</p>
                </div>
                @empty
                <p class="text-steel-500 text-sm">No active repairs.</p>
                @endforelse
            </div>
        </div>

        <div class="mt-8 text-center">
            <a href="{{ route('booking.create') }}" class="inline-flex px-6 py-3 bg-military-600 text-white font-semibold rounded-lg hover:bg-military-500 transition">Book New Service</a>
        </div>
    </div>
</section>
@endsection
