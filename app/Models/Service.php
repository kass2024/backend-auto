<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'icon', 'image',
        'price_from', 'duration_minutes', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'price_from' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function jobCards(): HasMany
    {
        return $this->hasMany(JobCard::class);
    }

    public function jobCardLines(): HasMany
    {
        return $this->hasMany(JobCardLine::class);
    }
}
