@extends('layouts.public')
@section('title', 'Request Quote | NEAMEE Auto-Tech Solutions')
@section('content')
<section class="py-16 bg-black">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h1 class="font-display text-4xl font-bold text-white mb-3">Request a Quote</h1>
            <p class="text-steel-400">Tell us about your vehicle and we'll provide a free estimate within 24 hours.</p>
        </div>
        <form method="POST" action="{{ route('quote.store') }}" class="space-y-6 bg-steel-900 border border-steel-800 rounded-2xl p-8">
            @csrf
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-steel-300 mb-2">Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="First and last name" class="w-full bg-steel-950 border-steel-700 text-white rounded-lg focus:border-military-500 focus:ring-military-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-steel-300 mb-2">Phone *</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" required placeholder="Phone number with area code" class="w-full bg-steel-950 border-steel-700 text-white rounded-lg focus:border-military-500 focus:ring-military-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-steel-300 mb-2">Email *</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="Email address" class="w-full bg-steel-950 border-steel-700 text-white rounded-lg focus:border-military-500 focus:ring-military-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-steel-300 mb-2">Service Needed</label>
                <select name="service_id" class="w-full bg-steel-950 border-steel-700 text-white rounded-lg focus:border-military-500 focus:ring-military-500">
                    <option value="">General inquiry</option>
                    @foreach($services as $service)
                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-steel-300 mb-2">Vehicle Make</label>
                    <input type="text" name="vehicle_make" value="{{ old('vehicle_make') }}" placeholder="Vehicle manufacturer" class="w-full bg-steel-950 border-steel-700 text-white rounded-lg focus:border-military-500 focus:ring-military-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-steel-300 mb-2">Vehicle Model</label>
                    <input type="text" name="vehicle_model" value="{{ old('vehicle_model') }}" placeholder="Vehicle model" class="w-full bg-steel-950 border-steel-700 text-white rounded-lg focus:border-military-500 focus:ring-military-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-steel-300 mb-2">Message</label>
                <textarea name="message" rows="4" class="w-full bg-steel-950 border-steel-700 text-white rounded-lg focus:border-military-500 focus:ring-military-500" placeholder="Describe the service or repair you need a quote for">{{ old('message') }}</textarea>
            </div>
            <button type="submit" class="w-full py-4 bg-military-600 text-white font-bold rounded-xl hover:bg-military-500 transition">Submit Quote Request</button>
        </form>
    </div>
</section>
@endsection
