<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImportItem extends Model
{
    /** @use HasFactory<\Database\Factories\ProductImportItemFactory> */
    use HasFactory;

    public function subTotal()
    {
        return $this->qty * $this->unit_price;
    }

    public function productImport(): BelongsTo
    {
        return $this->belongsTo(ProductImport::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
