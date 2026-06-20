@extends('layouts.public')
@section('title', 'Blog & Tips | NEAMEE Auto-Tech Solutions')
@section('content')
<section class="py-20 bg-black">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="font-display text-5xl font-bold text-white mb-4">Blog & Car Care Tips</h1>
        <p class="text-steel-400">Expert advice to keep your vehicle in top condition.</p>
    </div>
</section>
<section class="py-16 bg-steel-950 pb-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid md:grid-cols-3 gap-8">
        @foreach($posts as $post)
        <a href="{{ route('blog.show', $post->slug) }}" class="group block rounded-2xl overflow-hidden bg-steel-900 border border-steel-800 hover:border-military-600 transition">
            <div class="aspect-video overflow-hidden">
                <img src="https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=600&q=80" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
            </div>
            <div class="p-6">
                <span class="text-military-400 text-xs font-semibold uppercase">{{ $post->category }}</span>
                <h2 class="font-display text-xl font-bold text-white mt-2 group-hover:text-military-400 transition">{{ $post->title }}</h2>
                <p class="text-steel-400 text-sm mt-2">{{ $post->excerpt }}</p>
                <p class="text-steel-500 text-xs mt-4">{{ $post->published_at->format('M d, Y') }}</p>
            </div>
        </a>
        @endforeach
    </div>
    <div class="mt-12">{{ $posts->links() }}</div>
</section>
@endsection
