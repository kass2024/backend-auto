@extends('layouts.public')
@section('title', 'Contact Us | NEAMEE Auto-Tech Solutions')
@section('content')
<section class="py-20 bg-black">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h1 class="font-display text-5xl font-bold text-white mb-4">Contact Us</h1>
            <p class="text-steel-400">Visit our garage or reach out — we're here to help.</p>
        </div>
        <div class="grid lg:grid-cols-2 gap-12">
            <div class="space-y-8">
                <div class="p-6 rounded-2xl bg-steel-900 border border-steel-800">
                    <h3 class="font-display text-xl font-bold text-white mb-4">Visit Our Garage</h3>
                    <p class="text-steel-300">120 Bogle Lane<br>Bowling Green, KY 42101</p>
                </div>
                <div class="p-6 rounded-2xl bg-steel-900 border border-steel-800">
                    <h3 class="font-display text-xl font-bold text-white mb-4">Get In Touch</h3>
                    <p class="text-steel-300 space-y-2">
                        <a href="tel:+15673299231" class="block hover:text-military-400">Phone: +1 (567) 329-9231</a>
                        <a href="mailto:info@neamee-autotechsolutions.com" class="block hover:text-military-400">Email: info@neamee-autotechsolutions.com</a>
                    </p>
                </div>
                <div class="p-6 rounded-2xl bg-military-900/30 border border-military-700">
                    <h3 class="font-display text-xl font-bold text-white mb-4">Business Hours</h3>
                    <ul class="text-steel-300 text-sm space-y-1">
                        <li class="flex justify-between"><span>Mon - Fri</span><span>8:00 AM - 6:00 PM</span></li>
                        <li class="flex justify-between"><span>Saturday</span><span>9:00 AM - 4:00 PM</span></li>
                        <li class="flex justify-between"><span>Sunday</span><span>Closed</span></li>
                        <li class="text-military-400 font-medium mt-2">24/7 Emergency Support Available</li>
                    </ul>
                </div>
            </div>
            <div class="rounded-2xl overflow-hidden h-96 lg:h-auto min-h-[400px]">
                <iframe src="https://maps.google.com/maps?q=120+Bogle+Lane,+Bowling+Green,+KY+42101&output=embed" class="w-full h-full border-0" loading="lazy" title="NEAMEE Auto-Tech Location"></iframe>
            </div>
        </div>
    </div>
</section>
@endsection
