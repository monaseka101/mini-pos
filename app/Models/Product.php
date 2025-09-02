<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $attributes = [
        'stock' => 0
    ];
    public function scopeWithSoldCount($query)
    {
        return $query->withSum('saleItems', 'qty');
    }
    public function scopeTopSold($query)
    {
        return $query->withSum('saleItems', 'qty')
            ->orderByDesc('sale_items_sum_qty');
    }

    // Scope for Least Sold
    public function scopeLeastSold($query)
    {
        return $query->withSum('saleItems', 'qty')
            ->orderBy('sale_items_sum_qty');
    }

    // Helper scope to always include sold count for sorting




    public function productimportItems(): HasMany
    {
        // This is the REAL one, pointing to ProductImportItem
        return $this->hasMany(\App\Models\ProductImportItem::class, 'product_id')
            ->with('productImport')
            ->orderByDesc('created_at');
    }

    public function Productimports(): HasMany
    {
        // This is the old one, but it's not technically correct 
        // because product_imports doesn’t have product_id
        return $this->hasMany(ProductImport::class);
    }



    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
