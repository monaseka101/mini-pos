<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Sale extends Model
{
    /** @use HasFactory<\Database\Factories\SaleFactory> */
    use HasFactory;

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

    // Sale Data Summary for State
    public static function totalSaleForToday()
    {
        return static::query()
            ->whereDate('sale_date', today())
            ->with('items')
            ->get()
            ->sum(function (Sale $sale) {
                return $sale->total_price;
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

    public static function sortByTotalPrice(Builder $query, string $direction): Builder
    {
        return $query
            ->select('sales.*')
            ->leftJoinSub(
                SaleItem::select('sale_id')
                    ->selectRaw('SUM(qty * unit_price) as total_price')
                    ->groupBy('sale_id'),
                'items',
                'items.sale_id',
                '=',
                'sales.id'
            )
            ->orderBy('items.total_price', $direction);
    }
}
