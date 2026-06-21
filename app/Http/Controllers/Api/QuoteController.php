<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use App\Models\Service;
use App\Services\AdminNotificationService;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    public function create()
    {
        return response()->json([
            'services' => Service::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'service_id' => $request->input('service_id') ?: null,
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:30',
            'service_id' => 'nullable|exists:services,id',
            'vehicle_make' => 'nullable|string|max:100',
            'vehicle_model' => 'nullable|string|max:100',
            'message' => 'nullable|string|max:2000',
        ]);

        $quote = QuoteRequest::create([
            ...$validated,
            'status' => 'new',
        ]);

        app(AdminNotificationService::class)->notifyNewQuote($quote->fresh('service'));

        return response()->json([
            'message' => 'Quote request received. We will contact you within 24 hours.',
        ], 201);
    }
}
