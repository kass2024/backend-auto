@extends('layouts.public')
@section('title', 'Book Service | NEAMEE Auto-Tech Solutions')
@section('content')
<section class="py-16 bg-black">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h1 class="font-display text-4xl font-bold text-white mb-3">Book Your Service</h1>
            <p class="text-steel-400">Fill in the details below and we'll confirm your appointment.</p>
        </div>
        <form method="POST" action="{{ route('booking.store') }}" class="space-y-6 bg-steel-900 border border-steel-800 rounded-2xl p-8">
            @csrf
            <div>
                <label class="block text-sm font-medium text-steel-300 mb-2">Select Service *</label>
                <select name="service_id" required class="w-full bg-steel-950 border-steel-700 text-white rounded-lg focus:border-military-500 focus:ring-military-500">
                    <option value="">Choose a service...</option>
                    @foreach($services as $service)
                    <option value="{{ $service->id }}" @selected(old('service_id', request('service')) == $service->id)>{{ $service->name }} @if($service->price_from)(from ${{ number_format($service->price_from, 2) }})@endif</option>
                    @endforeach
                </select>
                @error('service_id')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-steel-300 mb-2">Full Name *</label>
                    <input type="text" name="customer_name" value="{{ old('customer_name', auth()->user()?->name) }}" required placeholder="First and last name" class="w-full bg-steel-950 border-steel-700 text-white rounded-lg focus:border-military-500 focus:ring-military-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-steel-300 mb-2">Phone *</label>
                    <input type="tel" name="customer_phone" value="{{ old('customer_phone', auth()->user()?->phone) }}" required placeholder="Phone number with area code" class="w-full bg-steel-950 border-steel-700 text-white rounded-lg focus:border-military-500 focus:ring-military-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-steel-300 mb-2">Email *</label>
                <input type="email" name="customer_email" value="{{ old('customer_email', auth()->user()?->email) }}" required placeholder="Email address" class="w-full bg-steel-950 border-steel-700 text-white rounded-lg focus:border-military-500 focus:ring-military-500">
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-steel-300 mb-2">Preferred Date *</label>
                    <input type="date" name="scheduled_date" value="{{ old('scheduled_date') }}" min="{{ date('Y-m-d') }}" required class="w-full bg-steel-950 border-steel-700 text-white rounded-lg focus:border-military-500 focus:ring-military-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-steel-300 mb-2">Preferred Time *</label>
                    <input type="time" name="scheduled_time" value="{{ old('scheduled_time', '09:00') }}" required class="w-full bg-steel-950 border-steel-700 text-white rounded-lg focus:border-military-500 focus:ring-military-500">
                </div>
            </div>
            @if($vehicles->count())
            <div>
                <label class="block text-sm font-medium text-steel-300 mb-2">Select Vehicle</label>
                <select name="vehicle_id" class="w-full bg-steel-950 border-steel-700 text-white rounded-lg focus:border-military-500 focus:ring-military-500">
                    <option value="">Add new vehicle below...</option>
                    @foreach($vehicles as $vehicle)
                    <option value="{{ $vehicle->id }}">{{ $vehicle->display_name }} ({{ $vehicle->plate_number }})</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="grid sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-steel-300 mb-2">Vehicle Make</label>
                    <input type="text" name="vehicle_make" value="{{ old('vehicle_make') }}" placeholder="Vehicle manufacturer" class="w-full bg-steel-950 border-steel-700 text-white rounded-lg focus:border-military-500 focus:ring-military-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-steel-300 mb-2">Vehicle Model</label>
                    <input type="text" name="vehicle_model" value="{{ old('vehicle_model') }}" placeholder="Vehicle model" class="w-full bg-steel-950 border-steel-700 text-white rounded-lg focus:border-military-500 focus:ring-military-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-steel-300 mb-2">Plate Number</label>
                    <input type="text" name="vehicle_plate" value="{{ old('vehicle_plate') }}" placeholder="License plate number" class="w-full bg-steel-950 border-steel-700 text-white rounded-lg focus:border-military-500 focus:ring-military-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-steel-300 mb-2">Additional Notes</label>
                <textarea name="notes" rows="3" class="w-full bg-steel-950 border-steel-700 text-white rounded-lg focus:border-military-500 focus:ring-military-500" placeholder="Symptoms, requests, or special instructions (optional)">{{ old('notes') }}</textarea>
            </div>
            <button type="submit" class="w-full py-4 bg-military-600 text-white font-bold rounded-xl hover:bg-military-500 transition">Submit Booking</button>
        </form>
    </div>
</section>
@endsection
