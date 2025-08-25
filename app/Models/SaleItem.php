<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    /** @use HasFactory<\Database\Factories\SaleItemFactory> */
    use HasFactory;

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function subTotal()
    {
        return $this->qty * $this->unit_price;
    }
    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }
    public function getHasDiscountAttribute(): string
    {
        return (is_null($this->discount_id) || intval($this->discount_id) === 1) ? 'No' : 'Yes';
    }
    public function getDiscountAmountAttribute(): float
    {
        if (!$this->discount || !$this->discount->active) {
            return 0;
        }

        $total = $this->qty * $this->unit_price;

        if ($this->discount->ispercent) {
            return $total * ($this->discount->value / 100);
        }

        // If not percent, treat it as fixed amount per item
        return $this->discount->value * $this->qty;
    }
}
