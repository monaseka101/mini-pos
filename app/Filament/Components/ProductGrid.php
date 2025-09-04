<?php

namespace App\Filament\Components;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use App\Models\Product;

class ProductGrid extends Component
{
    protected string $view = 'filament.components.product-grid';

    public static function make(string $name = 'product_grid'): static
    {
        return app(static::class, ['name' => $name]);
    }

    public function getProducts()
    {
        return Product::where('active', true)->where('stock', '>', 0)->get();
    }

    public function addToCartAction(Product $product): Action
    {
        return Action::make("add_to_cart_{$product->id}")
            ->label('Add to Cart')
            ->icon('heroicon-o-plus-circle')
            ->action(function (array $data, $livewire, $set, $get) use ($product) {
                $currentItems = $get('items') ?? [];

                // Check if product already exists
                $existingIndex = collect($currentItems)->search(function ($item) use ($product) {
                    return $item['product_id'] == $product->id;
                });

                if ($existingIndex !== false) {
                    // Increase quantity
                    $currentItems[$existingIndex]['qty']++;
                } else {
                    // Add new item
                    $currentItems[] = [
                        'product_id' => $product->id,
                        'qty' => 1,
                        'unit_price' => $product->price,
                        'discount' => 0,
                        'available_stock' => $product->stock,
                    ];
                }

                $set('items', $currentItems);

                // Show notification
                $livewire->notify('success', $product->name . ' added to cart!');
            });
    }
}

