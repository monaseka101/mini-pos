<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class ProductImport extends Model
{
    /** @use HasFactory<\Database\Factories\ProductImportFactory> */
    use HasFactory;

    public function totalPrice()
    {
        return $this->items->sum(function ($item) {
            return $item->subTotal();
        });
    }

    public function totalQty()
    {
        return $this->items()->sum('qty');
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

    public static function sortByTotalPrice(Builder $query, string $direction): Builder
    {
        // Use a subquery to calculate total_price per import
        return $query->select('product_imports.*')
            ->leftJoinSub(
                ProductImportItem::select('product_import_id')
                    ->selectRaw('SUM(qty * unit_price) as total_price')
                    ->groupBy('product_import_id'),
                'items',
                'items.product_import_id',
                '=',
                'product_imports.id'
            )
            ->orderBy('items.total_price', $direction);
    }

    // Expense Data Summary for State
    public static function totalExpenseForToday()
    {
        return static::query()
            ->whereDate('import_date', today())
            // ->with('items')
            ->get()
            ->sum(function (ProductImport $productImport) {
                return $productImport->totalPrice();
            });
    }

    public static function totalExpenseThisMonth()
    {
        return static::query()
            ->whereMonth('import_date', now()->month)
            ->whereYear('import_date', now()->year)            // ->with('items')
            ->get()
            ->sum(function (ProductImport $productImport) {
                return $productImport->totalPrice();
            });
    }

    public static function totalExpenseThisYear()
    {
        return static::query()
            ->whereYear('import_date', now()->year)            // ->with('items')
            ->get()
            ->sum(function (ProductImport $productImport) {
                return $productImport->totalPrice();
            });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProductImportItem::class);
    }
}
