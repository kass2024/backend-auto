@extends('layouts.public')
@section('title', $post->title . ' | NEAMEE Auto-Tech Solutions')
@section('content')
<article class="py-16 bg-steel-950 pb-24">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <a href="{{ route('blog.index') }}" class="text-military-400 text-sm hover:text-military-300 mb-6 inline-block">← Back to Blog</a>
        <span class="text-military-400 text-xs font-semibold uppercase">{{ $post->category }}</span>
        <h1 class="font-display text-4xl font-bold text-white mt-2 mb-4">{{ $post->title }}</h1>
        <p class="text-steel-500 text-sm mb-8">{{ $post->published_at->format('F d, Y') }}</p>
        <img src="https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=1200&q=80" alt="{{ $post->title }}" class="w-full rounded-2xl mb-8">
        <div class="prose prose-invert prose-steel max-w-none">
            <p class="text-steel-300 text-lg leading-relaxed">{{ $post->content }}</p>
        </div>
    </div>
</article>
@endsection
