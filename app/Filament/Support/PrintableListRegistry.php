<?php

namespace App\Filament\Support;

use App\Models\BlogPost;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Part;
use App\Models\Promotion;
use App\Models\QuoteRequest;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\User;

class PrintableListRegistry
{
    public static function get(string $key): ?array
    {
        return self::definitions()[$key] ?? null;
    }

    public static function definitions(): array
    {
        return [
            'parts' => [
                'title' => 'AUTOMOTIVE PARTS INVENTORY',
                'columns' => ['PART NO', 'CATEGORY', 'BRAND', 'MODEL', 'YEAR', 'PART NUMBER', 'QUANTITY', 'UNIT PRICE'],
                'rows' => fn () => Part::query()->orderBy('part_no')->get()->map(fn (Part $p) => [
                    $p->part_no ?? $p->sku ?? '—',
                    $p->category ?? '—',
                    $p->brand ?? '—',
                    $p->vehicle_model ?? '—',
                    $p->vehicle_year ?? '—',
                    $p->manufacturer_part_number ?? '—',
                    (string) ($p->quantity ?? 0),
                    Money::format($p->unit_price),
                ])->all(),
            ],
            'customers' => [
                'title' => 'CUSTOMER LIST',
                'columns' => ['NAME', 'EMAIL', 'PHONE', 'VEHICLES', 'INVOICES', 'POINTS'],
                'rows' => fn () => User::query()->where('role', 'customer')->with('vehicles')->withCount(['invoices'])->orderBy('name')->get()->map(fn (User $u) => [
                    $u->name,
                    $u->email,
                    $u->phone ?? '—',
                    $u->vehicles->pluck('plate_number')->join(', ') ?: '—',
                    (string) $u->invoices_count,
                    (string) ($u->loyalty_points ?? 0),
                ])->all(),
            ],
            'invoices' => [
                'title' => 'INVOICE LIST',
                'columns' => ['INVOICE #', 'CUSTOMER', 'TOTAL', 'STATUS', 'DUE DATE', 'CREATED'],
                'rows' => fn () => Invoice::query()->with('user')->latest()->get()->map(fn (Invoice $i) => [
                    $i->invoice_number,
                    $i->user?->name ?? '—',
                    Money::format($i->total),
                    ucfirst($i->status),
                    $i->due_date?->format('Y-m-d') ?? '—',
                    $i->created_at?->format('Y-m-d H:i') ?? '—',
                ])->all(),
            ],
            'bookings' => [
                'title' => 'BOOKINGS LIST',
                'columns' => ['REFERENCE', 'CUSTOMER', 'SERVICE', 'DATE', 'TIME', 'STATUS'],
                'rows' => fn () => Booking::query()->with('service')->orderByDesc('scheduled_date')->get()->map(fn (Booking $b) => [
                    $b->reference,
                    $b->customer_name,
                    $b->service?->name ?? '—',
                    $b->scheduled_date?->format('Y-m-d') ?? '—',
                    $b->scheduled_time ?? '—',
                    ucfirst($b->status),
                ])->all(),
            ],
            'services' => [
                'title' => 'SERVICES CATALOG',
                'columns' => ['ORDER', 'SERVICE', 'PRICE FROM', 'DURATION', 'ACTIVE'],
                'rows' => fn () => Service::query()->orderBy('sort_order')->get()->map(fn (Service $s) => [
                    (string) $s->sort_order,
                    $s->name,
                    Money::format($s->price_from),
                    $s->duration_minutes ? $s->duration_minutes.' min' : '—',
                    $s->is_active ? 'Yes' : 'No',
                ])->all(),
            ],
            'quote-requests' => [
                'title' => 'QUOTE REQUESTS',
                'columns' => ['#', 'CUSTOMER', 'EMAIL', 'SERVICE', 'STATUS', 'DATE'],
                'rows' => fn () => QuoteRequest::query()->with('service')->latest()->get()->map(fn (QuoteRequest $q) => [
                    (string) $q->id,
                    $q->name,
                    $q->email,
                    $q->service?->name ?? 'General',
                    ucfirst($q->status),
                    $q->created_at?->format('Y-m-d') ?? '—',
                ])->all(),
            ],
            'staff' => [
                'title' => 'STAFF LIST',
                'columns' => ['NAME', 'EMAIL', 'PHONE', 'JOINED'],
                'rows' => fn () => User::query()->where('role', 'staff')->orderBy('name')->get()->map(fn (User $u) => [
                    $u->name,
                    $u->email,
                    $u->phone ?? '—',
                    $u->created_at?->format('Y-m-d') ?? '—',
                ])->all(),
            ],
            'blog-posts' => [
                'title' => 'BLOG POSTS',
                'columns' => ['TITLE', 'CATEGORY', 'PUBLISHED', 'STATUS'],
                'rows' => fn () => BlogPost::query()->orderByDesc('published_at')->get()->map(fn (BlogPost $b) => [
                    $b->title,
                    $b->category ?? '—',
                    $b->published_at?->format('Y-m-d H:i') ?? '—',
                    $b->is_published ? 'Published' : 'Draft',
                ])->all(),
            ],
            'testimonials' => [
                'title' => 'TESTIMONIALS',
                'columns' => ['CUSTOMER', 'RATING', 'REVIEW', 'ACTIVE'],
                'rows' => fn () => Testimonial::query()->orderBy('customer_name')->get()->map(fn (Testimonial $t) => [
                    $t->customer_name,
                    (string) $t->rating,
                    $t->review,
                    $t->is_active ? 'Yes' : 'No',
                ])->all(),
            ],
            'promotions' => [
                'title' => 'PROMOTIONS',
                'columns' => ['TITLE', 'DISCOUNT %', 'DISCOUNT $', 'ENDS', 'ACTIVE'],
                'rows' => fn () => Promotion::query()->orderBy('title')->get()->map(fn (Promotion $p) => [
                    $p->title,
                    $p->discount_percent ? $p->discount_percent.'%' : '—',
                    $p->discount_amount ? Money::format($p->discount_amount) : '—',
                    $p->ends_at?->format('Y-m-d') ?? '—',
                    $p->is_active ? 'Yes' : 'No',
                ])->all(),
            ],
        ];
    }
}
