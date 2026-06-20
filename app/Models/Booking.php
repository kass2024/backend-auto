<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    protected $fillable = [
        'reference', 'user_id', 'vehicle_id', 'service_id', 'mechanic_id',
        'customer_name', 'customer_email', 'customer_phone',
        'scheduled_date', 'scheduled_time', 'notes', 'status',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
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

    public function jobCard(): HasOne
    {
        return $this->hasOne(JobCard::class);
    }
}
