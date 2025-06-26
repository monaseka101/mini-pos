<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    /** @use HasFactory<\Database\Factories\SaleFactory> */
    use HasFactory;

    public function totalPrice()
    {
        return collect($this->items)->reduce(fn($total, $item) => $total + $item->subTotal(), 0);
    }
    public function getHasDiscountAttribute(): string
    {
        // Check if any sale item has discount_id not null and not 1
        $hasDiscount = $this->items()->whereNotNull('discount_id')
            ->where('discount_id', '!=', 1)
            ->exists();

        return $hasDiscount ? 'Yes' : 'No';
    }

    public function totalPay()
    {
        return $this->items->sum(function ($item) {
            $unitPrice = $item->unit_price;
            $qty = $item->qty;
            $discountAmount = 0;

            if ($item->discount_id && $item->discount) {
                $discountModel = $item->discount;
                $discount = $discountModel->value;
                $isPercent = $discountModel->ispercent;

                $discountAmount = $isPercent
                    ? ($unitPrice * $discount / 100)
                    : $discount;
            }

            return ($unitPrice - $discountAmount) * $qty;
        });
    }
    // In Sale.php model
    public function getDiscountAmountAttribute()
    {
        return $this->items->sum(function ($item) {
            $unitPrice = $item->unit_price;
            $qty = $item->qty;
            $discountAmount = 0;

            if ($item->discount_id && $item->discount) {
                $discountModel = $item->discount;
                $discount = $discountModel->value;
                $isPercent = $discountModel->ispercent;

                $discountAmount = $isPercent
                    ? ($unitPrice * $discount / 100) * $qty
                    : $discount * $qty;
            }

            return $discountAmount;
        });
    }

    public function totalItemQty()
    {
        return collect($this->items)->reduce(fn($totalQty, $item) => $totalQty + $item->qty, 0);
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
