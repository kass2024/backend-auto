@extends('layouts.public')
@section('title', 'Our Services | NEAMEE Auto-Tech Solutions')
@section('content')
<section class="py-20 bg-gradient-to-b from-black to-steel-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="font-display text-5xl font-bold text-white mb-4">Our Services</h1>
        <p class="text-steel-400 max-w-2xl mx-auto">Professional automotive services backed by certified mechanics and genuine parts.</p>
    </div>
</section>
<section class="py-16 bg-steel-950 pb-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid md:grid-cols-2 gap-8">
        @php
        $images = [
            'engine-repair' => 'https://images.unsplash.com/photo-1486262715619-67b85e0b08d3?w=800&q=80',
            'oil-change' => 'https://images.unsplash.com/photo-1625047509248-ec889cbff17f?w=800&q=80',
            'brake-service' => 'https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?w=800&q=80',
            'transmission-repair' => 'https://images.unsplash.com/photo-1615909128947-aa1c4e5e8c2b?w=800&q=80',
            'electrical-diagnosis' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80',
            'wheel-alignment' => 'https://images.unsplash.com/photo-1597764696110-5592b12a0b0a?w=800&q=80',
            'tire-services' => 'https://images.unsplash.com/photo-1578844251758-2f71da645270?w=800&q=80',
            'car-wash-detailing' => 'https://images.unsplash.com/photo-1607860108855-64acf2078ed9?w=800&q=80',
            'ac-service' => 'https://images.unsplash.com/photo-1625047509168-8307362b2271?w=800&q=80',
            'battery-replacement' => 'https://images.unsplash.com/photo-1599305445671-ac291c95aaa9?w=800&q=80',
        ];
        @endphp
        @foreach($services as $service)
        <div class="flex flex-col sm:flex-row gap-6 p-6 rounded-2xl bg-steel-900 border border-steel-800 hover:border-military-600 transition">
            <img src="{{ $images[$service->slug] ?? 'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=800&q=80' }}" alt="{{ $service->name }}" class="w-full sm:w-48 h-40 object-cover rounded-xl flex-shrink-0">
            <div>
                <h2 class="font-display text-2xl font-bold text-white mb-2">{{ $service->name }}</h2>
                <p class="text-steel-400 text-sm mb-4">{{ $service->description }}</p>
                @if($service->price_from)
                <p class="text-military-400 font-semibold mb-4">Starting at ${{ number_format($service->price_from, 2) }}</p>
                @endif
                <a href="{{ route('booking.create') }}?service={{ $service->id }}" class="inline-flex px-5 py-2 bg-military-600 text-white text-sm font-semibold rounded-lg hover:bg-military-500 transition">Book This Service</a>
            </div>
        </div>
        @endforeach
    </div>
</section>
@endsection
