<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Quotation extends Model
{
    protected $fillable = [
        'quote_number', 'user_id', 'vehicle_id', 'invoice_id',
        'customer_name', 'customer_phone', 'customer_email',
        'vehicle_make', 'vehicle_model', 'vehicle_year', 'vehicle_vin', 'vehicle_plate',
        'problem_description', 'inspection_findings', 'proposed_repairs',
        'parts_total', 'labor_total', 'additional_total', 'subtotal',
        'discount', 'tax_rate', 'tax_amount', 'total',
        'status', 'issued_at', 'expires_at', 'public_view_token', 'customer_emailed_at',
        'signature_name', 'signature_data', 'signed_at', 'signer_ip',
        'payment_terms', 'warranty_terms', 'authorization_terms',
    ];

    protected $casts = [
        'parts_total' => 'decimal:2',
        'labor_total' => 'decimal:2',
        'additional_total' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'issued_at' => 'date',
        'expires_at' => 'date',
        'customer_emailed_at' => 'datetime',
        'signed_at' => 'datetime',
        'vehicle_year' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class)->orderBy('sort_order');
    }

    public function partItems(): HasMany
    {
        return $this->hasMany(QuotationItem::class)->where('type', 'part')->orderBy('sort_order');
    }

    public function laborItems(): HasMany
    {
        return $this->hasMany(QuotationItem::class)->where('type', 'labor')->orderBy('sort_order');
    }

    public function additionalItems(): HasMany
    {
        return $this->hasMany(QuotationItem::class)->where('type', 'additional')->orderBy('sort_order');
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted' && filled($this->signed_at);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast()
            && ! in_array($this->status, ['accepted', 'converted', 'declined'], true);
    }

    public function canBeSigned(): bool
    {
        return in_array($this->status, ['draft', 'sent', 'viewed'], true) && ! $this->isExpired();
    }

    public function canConvertToInvoice(): bool
    {
        return $this->isAccepted() && blank($this->invoice_id) && $this->status !== 'converted';
    }

    public function ensurePublicViewToken(): string
    {
        if (filled($this->public_view_token)) {
            return $this->public_view_token;
        }

        $token = Str::random(48);
        $this->forceFill(['public_view_token' => $token])->save();

        return $token;
    }

    public function isValidPublicViewToken(?string $token): bool
    {
        return filled($token)
            && filled($this->public_view_token)
            && hash_equals($this->public_view_token, $token);
    }

    public function publicUrl(): ?string
    {
        $token = $this->ensurePublicViewToken();

        return route('quotation.view', [
            'quotation' => $this->id,
            'token' => $token,
        ], absolute: true);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'sent' => 'Sent',
            'viewed' => 'Viewed',
            'accepted' => 'Accepted',
            'declined' => 'Declined',
            'expired' => 'Expired',
            'converted' => 'Converted to invoice',
            default => ucfirst($this->status),
        };
    }

    public static function defaultPaymentTerms(): string
    {
        return 'Payment is due upon completion of repairs unless otherwise agreed in writing. Late payments may incur a fee of 1.5% per month.';
    }

    public static function defaultWarrantyTerms(): string
    {
        return 'Parts and labor are warranted for 90 days or 3,000 miles, whichever comes first, unless otherwise stated for specific parts.';
    }

    public static function defaultAuthorizationTerms(): string
    {
        return 'By signing, the customer authorizes NEAMEE Auto-Tech to perform the proposed repairs and acknowledges that estimates may change if additional issues are discovered.';
    }
}
