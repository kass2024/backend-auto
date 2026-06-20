@extends('layouts.public')
@section('title', 'About Us | NEAMEE Auto-Tech Solutions')
@section('content')
<section class="py-20 bg-black">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <img src="{{ asset('images/logo/logo.png') }}" alt="NEAMEE" class="h-24 w-24 rounded-full mx-auto mb-8 ring-4 ring-military-600">
        <h1 class="font-display text-5xl font-bold text-white mb-6">About NEAMEE Auto-Tech Solutions</h1>
        <p class="text-steel-300 text-lg leading-relaxed mb-8">Located at 120 Bogle Lane in Bowling Green, Kentucky, NEAMEE Auto-Tech Solutions is your trusted partner for professional automotive repair and maintenance. Our certified mechanics combine years of experience with modern diagnostic equipment to deliver exceptional service.</p>
        <p class="text-steel-400 leading-relaxed">We use only genuine spare parts, offer warranty on all repairs, and provide 24/7 customer support including roadside assistance. Whether you need a simple oil change or complex engine repair, we're committed to keeping you safely on the road.</p>
    </div>
</section>
@endsection
