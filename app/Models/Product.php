<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
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

    protected function totalSoldNumber(): Attribute
    {
        return Attribute::make(
            get: fn() => SaleItem::query()
                ->where('product_id', $this->id)
                ->sum('qty')
        );
    }


    public function scopeWithSoldCount($query)
    {
        return $query->withSum('saleItems', 'qty');
    }



    public function importItems(): HasMany
    {
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

    public function productimportItems()
    {
        return $this->hasMany(\App\Models\ProductImportItem::class);
    }
}
