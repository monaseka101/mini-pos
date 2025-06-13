<?php

namespace App\Filament\Resources\ProductImportResource\Pages;

use App\Filament\Resources\ProductImportResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

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
        $items = $this->record->items;
        // Iterate over items
        foreach ($items as $item) {
            $product = Product::find($item->id);
            if ($product) {
                $product->increment('stock', $item->qty);
            }
        }
    }
}
