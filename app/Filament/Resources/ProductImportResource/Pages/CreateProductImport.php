<?php

namespace App\Filament\Resources\ProductImportResource\Pages;

use App\Filament\Resources\ProductImportResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateProductImport extends CreateRecord
{
    protected static string $resource = ProductImportResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        return $data;
    }

    protected function afterCreate()
    {
        Log::info($this->record->items);
        $items = $this->record->items;
        // // Iterate over items
        foreach ($items as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                $product->increment('stock', $item->qty);
            }
        }
    }
}
