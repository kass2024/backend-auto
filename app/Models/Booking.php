<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    protected $fillable = [
        'reference', 'user_id', 'vehicle_id', 'service_id', 'mechanic_id', 'job_card_id',
        'scheduled_by_user_id', 'customer_name', 'customer_email', 'customer_phone',
        'scheduled_date', 'scheduled_time', 'notes', 'staff_notes', 'status',
        'confirmation_sent_at', 'reminder_sent_at', 'popup_dismissed_at',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'confirmation_sent_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'popup_dismissed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function mechanic(): BelongsTo
    {
        return $this->belongsTo(Mechanic::class);
    }

    public function followUpFromJobCard(): BelongsTo
    {
        return $this->belongsTo(JobCard::class, 'job_card_id');
    }

    public function scheduledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scheduled_by_user_id');
    }

    public function jobCard(): HasOne
    {
        return $this->hasOne(JobCard::class);
    }
}
