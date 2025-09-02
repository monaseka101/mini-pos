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

    public static function sortByTotalPrice(Builder $query, string $direction)
    {
        return $query->join('product_import_items', 'product_imports.id', '=', 'product_import_items.product_import_id')
            ->groupBy('product_imports.id')
            ->selectRaw('product_imports.*, SUM(product_import_items.qty * product_import_items.unit_price)as total_price')
            ->orderBy('total_price', $direction);
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
