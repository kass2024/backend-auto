<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="NEAMEE Auto-Tech Solutions - Professional car garage services in Bowling Green, KY. Engine repair, oil change, brake service, and more.">
    <title>@yield('title', 'NEAMEE Auto-Tech Solutions')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="font-sans antialiased bg-steel-950 text-steel-100">
    {{-- Top bar --}}
    <div class="bg-black border-b border-steel-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 flex flex-wrap items-center justify-between gap-2 text-sm">
            <div class="flex items-center gap-4 text-steel-400">
                <a href="tel:+15673299231" class="hover:text-military-400 transition flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    +1 (567) 329-9231
                </a>
                <span class="hidden sm:inline text-steel-600">|</span>
                <span class="hidden sm:inline">120 Bogle Lane, Bowling Green, KY 42101</span>
            </div>
            <div class="flex items-center gap-3">
                <a href="https://wa.me/15673299231" target="_blank" class="text-green-400 hover:text-green-300 transition font-medium">WhatsApp</a>
                @auth
                    <a href="{{ route('portal.dashboard') }}" class="text-military-400 hover:text-military-300">My Portal</a>
                @else
                    <a href="{{ route('login') }}" class="text-steel-400 hover:text-white">Login</a>
                @endauth
            </div>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="sticky top-0 z-50 bg-steel-950/95 backdrop-blur-md border-b border-steel-800" x-data="{ open: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    <img src="{{ asset('images/logo/logo.png') }}" alt="NEAMEE Auto-Tech Solutions" class="h-14 w-14 rounded-full ring-2 ring-military-600 group-hover:ring-military-400 transition">
                    <div class="hidden sm:block">
                        <span class="font-display text-xl font-bold text-white tracking-wide">NEAMEE</span>
                        <span class="block text-xs text-military-400 font-semibold tracking-widest uppercase">Auto-Tech Solutions</span>
                    </div>
                </a>

                <div class="hidden lg:flex items-center gap-8">
                    <a href="{{ route('home') }}" class="text-sm font-medium {{ request()->routeIs('home') ? 'text-military-400' : 'text-steel-300 hover:text-white' }} transition">Home</a>
                    <a href="{{ route('services') }}" class="text-sm font-medium {{ request()->routeIs('services') ? 'text-military-400' : 'text-steel-300 hover:text-white' }} transition">Services</a>
                    <a href="{{ route('booking.create') }}" class="text-sm font-medium {{ request()->routeIs('booking.*') ? 'text-military-400' : 'text-steel-300 hover:text-white' }} transition">Book Now</a>
                    <a href="{{ route('blog.index') }}" class="text-sm font-medium {{ request()->routeIs('blog.*') ? 'text-military-400' : 'text-steel-300 hover:text-white' }} transition">Blog & Tips</a>
                    <a href="{{ route('contact') }}" class="text-sm font-medium {{ request()->routeIs('contact') ? 'text-military-400' : 'text-steel-300 hover:text-white' }} transition">Contact</a>
                </div>

                <div class="hidden lg:flex items-center gap-3">
                    <a href="{{ route('quote.create') }}" class="px-5 py-2.5 text-sm font-semibold border border-steel-600 text-steel-200 rounded-lg hover:border-military-500 hover:text-military-400 transition">Request Quote</a>
                    <a href="{{ route('booking.create') }}" class="px-5 py-2.5 text-sm font-semibold bg-military-600 text-white rounded-lg hover:bg-military-500 transition shadow-lg shadow-military-900/50">Book Service</a>
                </div>

                <button @click="open = !open" class="lg:hidden p-2 text-steel-300 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>

            <div x-show="open" x-cloak class="lg:hidden pb-4 space-y-2">
                <a href="{{ route('home') }}" class="block py-2 text-steel-300 hover:text-white">Home</a>
                <a href="{{ route('services') }}" class="block py-2 text-steel-300 hover:text-white">Services</a>
                <a href="{{ route('booking.create') }}" class="block py-2 text-steel-300 hover:text-white">Book Now</a>
                <a href="{{ route('blog.index') }}" class="block py-2 text-steel-300 hover:text-white">Blog & Tips</a>
                <a href="{{ route('contact') }}" class="block py-2 text-steel-300 hover:text-white">Contact</a>
                <a href="{{ route('quote.create') }}" class="block py-2 text-military-400">Request Quote</a>
            </div>
        </div>
    </nav>

    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-military-900/50 border border-military-600 text-military-200 px-4 py-3 rounded-lg">{{ session('success') }}</div>
        </div>
    @endif

    <main>@yield('content')</main>

    {{-- Emergency Assistance Bar --}}
    <div class="fixed bottom-0 left-0 right-0 z-40 lg:hidden bg-black/95 border-t border-military-700 p-3">
        <div class="flex gap-2">
            <a href="tel:+15673299231" class="flex-1 flex items-center justify-center gap-2 py-3 bg-military-600 text-white rounded-lg font-semibold text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                Call Now
            </a>
            <a href="https://wa.me/15673299231" class="flex-1 flex items-center justify-center gap-2 py-3 bg-green-600 text-white rounded-lg font-semibold text-sm">WhatsApp</a>
        </div>
    </div>

    {{-- Footer --}}
    <footer class="bg-black border-t border-steel-800 mt-20 pb-24 lg:pb-0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
                <div>
                    <img src="{{ asset('images/logo/logo.png') }}" alt="Logo" class="h-16 w-16 rounded-full mb-4">
                    <p class="text-steel-400 text-sm leading-relaxed">Professional automotive repair and maintenance services. Certified mechanics, genuine parts, and warranty on all repairs.</p>
                </div>
                <div>
                    <h4 class="font-display text-lg font-semibold text-white mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-sm text-steel-400">
                        <li><a href="{{ route('services') }}" class="hover:text-military-400 transition">Our Services</a></li>
                        <li><a href="{{ route('booking.create') }}" class="hover:text-military-400 transition">Book Appointment</a></li>
                        <li><a href="{{ route('quote.create') }}" class="hover:text-military-400 transition">Request Quote</a></li>
                        <li><a href="{{ route('blog.index') }}" class="hover:text-military-400 transition">Blog & Tips</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-display text-lg font-semibold text-white mb-4">Services</h4>
                    <ul class="space-y-2 text-sm text-steel-400">
                        <li>Engine Repair</li>
                        <li>Brake Service</li>
                        <li>Oil Change</li>
                        <li>Transmission Repair</li>
                        <li>Car Wash & Detailing</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-display text-lg font-semibold text-white mb-4">Contact Us</h4>
                    <ul class="space-y-3 text-sm text-steel-400">
                        <li>120 Bogle Lane<br>Bowling Green, KY 42101</li>
                        <li><a href="mailto:info@neamee-autotechsolutions.com" class="hover:text-military-400">info@neamee-autotechsolutions.com</a></li>
                        <li><a href="tel:+15673299231" class="hover:text-military-400">+1 (567) 329-9231</a></li>
                        <li class="text-military-400 font-medium">24/7 Customer Support</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-steel-800 mt-12 pt-8 flex flex-col sm:flex-row justify-between items-center gap-4 text-sm text-steel-500">
                <p>&copy; {{ date('Y') }} NEAMEE Auto-Tech Solutions. All rights reserved.</p>
                <a href="/admin" class="hover:text-steel-300 transition">Staff Login</a>
            </div>
        </div>
    </footer>
</body>
</html>
