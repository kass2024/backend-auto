<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JobCard extends Model
{
    protected $fillable = [
        'job_number', 'booking_id', 'user_id', 'vehicle_id', 'mechanic_id',
        'inspection_notes', 'checklist', 'labor_cost', 'parts_cost', 'total_cost',
        'status', 'started_at', 'completed_at',
    ];

    protected $casts = [
        'checklist' => 'array',
        'labor_cost' => 'decimal:2',
        'parts_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function mechanic(): BelongsTo
    {
        return $this->belongsTo(Mechanic::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(JobCardPhoto::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'waiting' => 'Waiting',
            'diagnosing' => 'Diagnosing',
            'parts_ordered' => 'Parts Ordered',
            'in_progress' => 'In Progress',
            'quality_check' => 'Quality Check',
            'ready_for_pickup' => 'Ready for Pickup',
            'delivered' => 'Delivered',
            default => ucfirst($status),
        };
    }
}
