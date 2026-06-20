@extends('layouts.public')
@section('title', 'Repair Tracking | Customer Portal')
@section('content')
<section class="py-12 bg-black min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="font-display text-3xl font-bold text-white mb-8">Repair Tracking</h1>
        @forelse($jobCards as $job)
        <div class="mb-6 p-6 bg-steel-900 border border-steel-800 rounded-2xl">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-white font-semibold">{{ $job->vehicle->display_name }}</p>
                    <p class="text-steel-500 text-sm">Job #{{ $job->job_number }}</p>
                </div>
                <span class="px-3 py-1 bg-military-900 text-military-300 text-sm rounded-full">{{ \App\Models\JobCard::statusLabel($job->status) }}</span>
            </div>
            @php
            $steps = ['waiting', 'diagnosing', 'parts_ordered', 'in_progress', 'quality_check', 'ready_for_pickup', 'delivered'];
            $currentIndex = array_search($job->status, $steps);
            @endphp
            <div class="flex gap-1">
                @foreach($steps as $i => $step)
                <div class="flex-1 h-2 rounded-full {{ $i <= $currentIndex ? 'bg-military-500' : 'bg-steel-800' }}"></div>
                @endforeach
            </div>
        </div>
        @empty
        <p class="text-steel-500">No repair jobs to track.</p>
        @endforelse
        <a href="{{ route('portal.dashboard') }}" class="text-military-400 hover:text-military-300 text-sm">← Back to Dashboard</a>
    </div>
</section>
@endsection
