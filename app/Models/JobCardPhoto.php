<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobCardPhoto extends Model
{
    protected $fillable = ['job_card_id', 'path', 'caption'];

    public function jobCard(): BelongsTo
    {
        return $this->belongsTo(JobCard::class);
    }
}
