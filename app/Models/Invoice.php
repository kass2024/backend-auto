<?php

namespace App\Models;

use App\Services\InvoiceServiceReminderService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number', 'user_id', 'job_card_id', 'vehicle_id',
        'subtotal', 'labor_total', 'parts_total', 'tax_rate', 'tax_amount', 'discount', 'total',
        'status', 'payment_method', 'paid_at', 'customer_emailed_at', 'public_view_token', 'due_date', 'work_description',
        'stripe_checkout_session_id', 'stripe_payment_url',
        'time_received', 'time_promised', 'odometer',
        'next_service_at', 'next_service_reminder_unit', 'next_service_repeat', 'next_service_timezone', 'next_service_notes',
        'next_service_reminder_early_sent_at', 'next_service_reminder_due_sent_at',
        'next_service_popup_dismissed_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'labor_total' => 'decimal:2',
        'parts_total' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
        'customer_emailed_at' => 'datetime',
        'due_date' => 'date',
        'time_received' => 'datetime',
        'time_promised' => 'datetime',
        'next_service_at' => 'datetime',
        'next_service_reminder_early_sent_at' => 'datetime',
        'next_service_reminder_due_sent_at' => 'datetime',
        'next_service_popup_dismissed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobCard(): BelongsTo
    {
        return $this->belongsTo(JobCard::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    public function partItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->where('type', 'part')->orderBy('sort_order');
    }

    public function serviceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->where('type', 'service')->orderBy('sort_order');
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isUnpaid(): bool
    {
        return in_array($this->status, ['sent', 'overdue', 'draft'], true);
    }

    public function paymentMethodLabel(): ?string
    {
        return match ($this->payment_method) {
            'cash' => 'Cash',
            'check' => 'Check',
            'bank_transfer' => 'Bank Transfer',
            'credit_card' => 'Credit Card (Stripe)',
            'mobile_money' => 'Mobile Money',
            default => null,
        };
    }

    /** Whether customer emails should include a Stripe payment link. */
    public function wantsStripePayment(): bool
    {
        return ! $this->isPaid() && $this->payment_method === 'credit_card';
    }

    public function wasEmailedToCustomer(): bool
    {
        if (! Schema::hasColumn($this->getTable(), 'customer_emailed_at')) {
            return false;
        }

        return $this->customer_emailed_at !== null;
    }

    public function markEmailedToCustomer(): void
    {
        if (! Schema::hasColumn($this->getTable(), 'customer_emailed_at')) {
            return;
        }

        try {
            $timestamp = now();

            $this->newQuery()
                ->whereKey($this->getKey())
                ->update(['customer_emailed_at' => $timestamp]);

            $this->setAttribute('customer_emailed_at', $timestamp);
            $this->syncOriginalAttribute('customer_emailed_at');
        } catch (\Throwable) {
            // Column may be missing if migrations have not run yet — email still sent.
        }
    }

    public function ensurePublicViewToken(): ?string
    {
        if (! Schema::hasColumn($this->getTable(), 'public_view_token')) {
            return null;
        }

        if (filled($this->public_view_token)) {
            return $this->public_view_token;
        }

        $token = Str::random(48);

        try {
            $this->newQuery()
                ->whereKey($this->getKey())
                ->update(['public_view_token' => $token]);

            $this->setAttribute('public_view_token', $token);
            $this->syncOriginalAttribute('public_view_token');
        } catch (\Throwable) {
            return null;
        }

        return $token;
    }

    public function hasServiceReminder(): bool
    {
        return $this->next_service_at !== null && filled($this->next_service_reminder_unit);
    }

    public function serviceReminderTimezone(): string
    {
        return $this->next_service_timezone ?: InvoiceServiceReminderService::defaultTimezone();
    }

    public function nextServiceAtLocal(): ?\Illuminate\Support\Carbon
    {
        if (! $this->next_service_at) {
            return null;
        }

        return $this->next_service_at->copy()->timezone($this->serviceReminderTimezone());
    }

    public function isValidPublicViewToken(?string $token): bool
    {
        if (blank($token) || ! Schema::hasColumn($this->getTable(), 'public_view_token')) {
            return false;
        }

        return filled($this->public_view_token) && hash_equals($this->public_view_token, $token);
    }
}
