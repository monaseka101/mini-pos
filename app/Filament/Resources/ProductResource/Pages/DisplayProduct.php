<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Resources\Pages\Page;

class DisplayProduct extends Page
{
    protected static string $resource = ProductResource::class;

    protected static string $view = 'filament.resources.product-resource.pages.display-product';

    public ?Product $product;

    public function mount(int $record): void
    {
        $this->product = Product::findOrFail($record);
    }

    protected function getViewData(): array
    {
        return [
            'product' => $this->product,
        ];
    }


}
