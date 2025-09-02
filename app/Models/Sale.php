<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

<<<<<<< HEAD
    // public function totalPrice()
    // {
    //     return collect($this->items)
    //         ->reduce(fn($total, $item) => $total + $item->subTotal(), 0);
    // }

    protected function totalPrice(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->items->sum(fn($item) => $item->subTotal())
        );
    }

    protected function totalQty(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->items->sum('qty')
        );
=======
    /**
     * Calculate the total price before discount.
     * Used for displaying original price.
     */
    public function totalPrice(): float
    {
        return $this->items->reduce(fn($total, $item) => $total + $item->subTotal(), 0);
    }

    /**
     * Check if the sale has any valid discount (discount_id not null and not 1).
     * Used in table view to show "Yes" or "No".
     */
    public function getHasDiscountAttribute(): string
    {
        return $this->items()
            ->whereNotNull('discount_id')
            ->where('discount_id', '!=', 1)
            ->exists() ? 'Yes' : 'No';
    }

    /**
     * Compute the total payable amount after discounts.
     * Used for invoice/receipt or saving to `total_pay` field.
     */
    public function totalPay(): float
    {
        return $this->items->sum(function ($item) {
            $unitPrice = $item->unit_price;
            $qty = $item->qty;

            // If the item has a valid discount
            if ($item->discount_id && $item->discount) {
                $discount = $item->discount->value;
                $isPercent = $item->discount->ispercent;

                $discountAmount = $isPercent
                    ? $unitPrice * $discount / 100
                    : $discount;

                return ($unitPrice - $discountAmount) * $qty;
            }

            return $unitPrice * $qty;
        });
    }

    /**
     * Get the total discount amount applied to this sale.
     * Used in receipt breakdown.
     */
    public function getDiscountAmountAttribute(): float
    {
        return $this->items->sum(function ($item) {
            if ($item->discount_id && $item->discount) {
                $discount = $item->discount->value;
                $isPercent = $item->discount->ispercent;
                $qty = $item->qty;
                $unitPrice = $item->unit_price;

                return $isPercent
                    ? ($unitPrice * $discount / 100) * $qty
                    : $discount * $qty;
            }

            return 0;
        });
    }

    /**
     * Get total quantity of all items in this sale.
     * Useful for summary widgets.
     */
    public function totalItemQty(): int
    {
        return $this->items->sum('qty');
>>>>>>> 8c30c670a9ec1afb31c671cb61f24a17e45bfe73
    }

    public function listProducts()
    {
        return $this->items()
            ->with('product')
            ->get()
            ->pluck('product.name')
            ->filter()
            ->join(', ');
    }

    public static function totalSaleForToday()
    {
        return static::query()
            ->whereDate('sale_date', today())
            ->with('items')
            ->get()
            ->sum(function (Sale $sale) {
                return $sale->totalPrice;
            });
    }

    public static function totalSaleForThisMonth()
    {
        return static::query()
            ->whereMonth('sale_date', now()->month)
            ->whereYear('sale_date', now()->year)
            ->with('items')
            ->get()
            ->sum(function (Sale $sale) {
                return $sale->total_price;
            });
    }

    public static function totalSaleForThisYear()
    {
        return static::query()
            ->whereYear('sale_date', now()->year)
            ->with('items')
            ->get()
            ->sum(function (Sale $sale) {
                return $sale->totalPrice;
            });
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


    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
