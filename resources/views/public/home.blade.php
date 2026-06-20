@extends('layouts.public')

@section('title', 'NEAMEE Auto-Tech Solutions | Professional Car Garage in Bowling Green, KY')

@section('content')
{{-- Hero Section --}}
<section class="relative min-h-[90vh] flex items-center overflow-hidden">
    <div class="absolute inset-0">
        <img src="https://images.unsplash.com/photo-1632823471565-1ecdf327735c?w=1920&q=80" alt="Professional auto repair garage" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-r from-black via-black/85 to-black/40"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-steel-950 via-transparent to-transparent"></div>
    </div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 w-full">
        <div class="max-w-2xl">
            <span class="inline-block px-4 py-1.5 bg-military-600/30 border border-military-500/50 text-military-300 text-sm font-semibold rounded-full mb-6">Bowling Green's Trusted Auto Experts</span>
            <h1 class="font-display text-5xl sm:text-6xl lg:text-7xl font-bold text-white leading-tight mb-6">
                Expert Car Care<br>
                <span class="text-military-400">You Can Trust</span>
            </h1>
            <p class="text-lg text-steel-300 mb-8 leading-relaxed">Certified mechanics, genuine spare parts, and fast service. From engine repair to detailing — we keep your vehicle running at its best.</p>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('booking.create') }}" class="px-8 py-4 bg-military-600 text-white font-semibold rounded-xl hover:bg-military-500 transition shadow-xl shadow-military-900/40 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Book Service Now
                </a>
                <a href="{{ route('quote.create') }}" class="px-8 py-4 border-2 border-steel-500 text-steel-200 font-semibold rounded-xl hover:border-military-500 hover:text-military-400 transition">Request Quote</a>
                <a href="{{ route('contact') }}" class="px-8 py-4 text-steel-400 font-semibold hover:text-white transition flex items-center gap-2">
                    Contact Us
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- Promotions --}}
@if($promotions->count())
<section class="py-12 bg-military-950 border-y border-military-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-{{ min($promotions->count(), 3) }} gap-6">
            @foreach($promotions as $promo)
            <div class="relative group overflow-hidden rounded-2xl bg-gradient-to-br from-military-800 to-steel-900 p-6 border border-military-700 hover:border-military-500 transition">
                <div class="absolute top-0 right-0 w-32 h-32 bg-military-600/20 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <span class="inline-block px-3 py-1 bg-military-600 text-white text-xs font-bold rounded-full mb-3">
                    @if($promo->discount_percent) {{ $promo->discount_percent }}% OFF @else SPECIAL OFFER @endif
                </span>
                <h3 class="font-display text-xl font-bold text-white mb-2">{{ $promo->title }}</h3>
                <p class="text-steel-300 text-sm">{{ $promo->description }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Services Section --}}
<section class="py-24 bg-steel-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="text-military-400 font-semibold text-sm uppercase tracking-widest">What We Offer</span>
            <h2 class="font-display text-4xl sm:text-5xl font-bold text-white mt-3 mb-4">Our Services</h2>
            <p class="text-steel-400 max-w-2xl mx-auto">Complete automotive care under one roof. Professional service for every make and model.</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6">
            @php
            $serviceImages = [
                'engine-repair' => 'https://images.unsplash.com/photo-1486262715619-67b85e0b08d3?w=400&q=80',
                'oil-change' => 'https://images.unsplash.com/photo-1625047509248-ec889cbff17f?w=400&q=80',
                'brake-service' => 'https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?w=400&q=80',
                'transmission-repair' => 'https://images.unsplash.com/photo-1615909128947-aa1c4e5e8c2b?w=400&q=80',
                'electrical-diagnosis' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&q=80',
                'wheel-alignment' => 'https://images.unsplash.com/photo-1597764696110-5592b12a0b0a?w=400&q=80',
                'tire-services' => 'https://images.unsplash.com/photo-1578844251758-2f71da645270?w=400&q=80',
                'car-wash-detailing' => 'https://images.unsplash.com/photo-1607860108855-64acf2078ed9?w=400&q=80',
                'ac-service' => 'https://images.unsplash.com/photo-1625047509168-8307362b2271?w=400&q=80',
                'battery-replacement' => 'https://images.unsplash.com/photo-1599305445671-ac291c95aaa9?w=400&q=80',
            ];
            @endphp
            @foreach($services as $service)
            <div class="group relative overflow-hidden rounded-2xl bg-steel-900 border border-steel-800 hover:border-military-600 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-military-900/20">
                <div class="aspect-[4/3] overflow-hidden">
                    <img src="{{ $serviceImages[$service->slug] ?? 'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=400&q=80' }}" alt="{{ $service->name }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
                </div>
                <div class="p-5">
                    <h3 class="font-display text-lg font-semibold text-white mb-1">{{ $service->name }}</h3>
                    @if($service->price_from)
                    <p class="text-military-400 text-sm font-medium">From ${{ number_format($service->price_from, 2) }}</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        <div class="text-center mt-12">
            <a href="{{ route('services') }}" class="inline-flex items-center gap-2 px-6 py-3 border border-military-600 text-military-400 rounded-lg hover:bg-military-600 hover:text-white transition font-semibold">
                View All Services
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
    </div>
</section>

{{-- Why Choose Us --}}
<section class="py-24 bg-black relative overflow-hidden">
    <div class="absolute inset-0 opacity-10">
        <img src="https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=1920&q=80" alt="" class="w-full h-full object-cover">
    </div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
            <div>
                <span class="text-military-400 font-semibold text-sm uppercase tracking-widest">Why NEAMEE</span>
                <h2 class="font-display text-4xl sm:text-5xl font-bold text-white mt-3 mb-6">Why Choose Us</h2>
                <p class="text-steel-400 mb-8">We combine decades of expertise with modern technology to deliver exceptional automotive service every time.</p>
                <div class="grid sm:grid-cols-2 gap-4">
                    @foreach([
                        ['Certified Mechanics', 'ASE-certified technicians with years of experience', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                        ['Genuine Spare Parts', 'OEM and premium aftermarket parts only', 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                        ['Fast Service', 'Efficient turnaround without compromising quality', 'M13 10V3L4 14h7v7l9-11h-7z'],
                        ['Affordable Pricing', 'Transparent quotes with no hidden fees', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['Warranty on Repairs', 'Peace of mind with our service guarantee', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['24/7 Support', 'Always here when you need roadside assistance', 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z'],
                    ] as [$title, $desc, $path])
                    <div class="flex gap-4 p-4 rounded-xl bg-steel-900/50 border border-steel-800">
                        <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-military-600/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-military-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $path }}"/></svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-white text-sm">{{ $title }}</h4>
                            <p class="text-steel-500 text-xs mt-1">{{ $desc }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="relative">
                <img src="https://images.unsplash.com/photo-1487754180451-c456f719a1fc?w=800&q=80" alt="Mechanic at work" class="rounded-2xl shadow-2xl">
                <div class="absolute -bottom-6 -left-6 bg-military-600 text-white p-6 rounded-2xl shadow-xl">
                    <div class="font-display text-4xl font-bold">15+</div>
                    <div class="text-sm text-military-100">Years Experience</div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Gallery --}}
<section class="py-24 bg-steel-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="text-military-400 font-semibold text-sm uppercase tracking-widest">Our Work</span>
            <h2 class="font-display text-4xl sm:text-5xl font-bold text-white mt-3">Before & After Gallery</h2>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($gallery as $image)
            <div class="group relative aspect-square overflow-hidden rounded-xl">
                <img src="{{ $image->image }}" alt="{{ $image->title }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/60 transition flex items-end">
                    <div class="p-4 translate-y-full group-hover:translate-y-0 transition">
                        <span class="text-xs uppercase tracking-wider text-military-400">{{ $image->type }}</span>
                        <p class="text-white text-sm font-medium">{{ $image->title }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Testimonials --}}
<section class="py-24 bg-black">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="text-military-400 font-semibold text-sm uppercase tracking-widest">Testimonials</span>
            <h2 class="font-display text-4xl sm:text-5xl font-bold text-white mt-3">What Our Customers Say</h2>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($testimonials as $testimonial)
            <div class="p-6 rounded-2xl bg-steel-900 border border-steel-800 hover:border-military-700 transition">
                <div class="flex gap-1 mb-4">
                    @for($i = 0; $i < $testimonial->rating; $i++)
                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    @endfor
                </div>
                <p class="text-steel-300 text-sm leading-relaxed mb-4">"{{ $testimonial->review }}"</p>
                <div>
                    <p class="font-semibold text-white text-sm">{{ $testimonial->customer_name }}</p>
                    @if($testimonial->vehicle_info)
                    <p class="text-steel-500 text-xs">{{ $testimonial->vehicle_info }}</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Online Booking CTA --}}
<section class="py-24 bg-gradient-to-br from-military-900 to-steel-950 relative overflow-hidden">
    <div class="absolute inset-0 opacity-20">
        <img src="https://images.unsplash.com/photo-1615909128947-aa1c4e5e8c2b?w=1920&q=80" alt="" class="w-full h-full object-cover">
    </div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <h2 class="font-display text-4xl font-bold text-white mb-4">Book Your Service Online</h2>
                <p class="text-steel-300 mb-6">Select your service, pick a date and time, and we'll take care of the rest. Fast, easy, and convenient.</p>
                <ul class="space-y-3 text-steel-300 text-sm mb-8">
                    <li class="flex items-center gap-2"><svg class="w-5 h-5 text-military-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Select service & preferred date/time</li>
                    <li class="flex items-center gap-2"><svg class="w-5 h-5 text-military-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Choose your vehicle or add a new one</li>
                    <li class="flex items-center gap-2"><svg class="w-5 h-5 text-military-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Track repair progress in real-time</li>
                </ul>
                <a href="{{ route('booking.create') }}" class="inline-flex px-8 py-4 bg-white text-steel-900 font-bold rounded-xl hover:bg-military-100 transition">Schedule Appointment</a>
            </div>
            <div class="bg-steel-900/80 backdrop-blur border border-steel-700 rounded-2xl p-8">
                <h3 class="font-display text-xl font-bold text-white mb-6">Emergency Assistance</h3>
                <p class="text-steel-400 text-sm mb-6">Stranded on the road? Our team is available 24/7 for roadside assistance.</p>
                <div class="space-y-3">
                    <a href="tel:+15673299231" class="flex items-center justify-center gap-3 w-full py-4 bg-military-600 text-white rounded-xl font-semibold hover:bg-military-500 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        Call Now: +1 (567) 329-9231
                    </a>
                    <a href="https://wa.me/15673299231" target="_blank" class="flex items-center justify-center gap-3 w-full py-4 bg-green-600 text-white rounded-xl font-semibold hover:bg-green-500 transition">
                        WhatsApp Support
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Blog --}}
@if($blogPosts->count())
<section class="py-24 bg-steel-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-12 gap-4">
            <div>
                <span class="text-military-400 font-semibold text-sm uppercase tracking-widest">Blog & Tips</span>
                <h2 class="font-display text-4xl font-bold text-white mt-3">Car Care Advice</h2>
            </div>
            <a href="{{ route('blog.index') }}" class="text-military-400 hover:text-military-300 font-semibold text-sm">View All Articles →</a>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            @foreach($blogPosts as $post)
            <a href="{{ route('blog.show', $post->slug) }}" class="group block rounded-2xl overflow-hidden bg-steel-900 border border-steel-800 hover:border-military-600 transition">
                <div class="aspect-video bg-steel-800 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=600&q=80" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                </div>
                <div class="p-6">
                    <span class="text-military-400 text-xs font-semibold uppercase">{{ $post->category }}</span>
                    <h3 class="font-display text-lg font-semibold text-white mt-2 group-hover:text-military-400 transition">{{ $post->title }}</h3>
                    <p class="text-steel-400 text-sm mt-2 line-clamp-2">{{ $post->excerpt }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
