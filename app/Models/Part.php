<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Part extends Model
{
    protected $fillable = [
        'sku', 'name', 'description', 'barcode', 'supplier_id',
        'quantity', 'min_stock', 'unit_cost', 'unit_price', 'location', 'is_active',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->min_stock;
    }
}
