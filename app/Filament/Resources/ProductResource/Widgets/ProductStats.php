<?php

namespace App\Filament\Resources\ProductResource\Widgets;

use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Models\Product;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB as FacadesDB;

class ProductStats extends BaseWidget
{
    use InteractsWithPageTable;
    // Disable polling (manual refresh only)
    protected static ?string $pollingInterval = null;

    /**
     * Define which page this widget is tied to
     */
    protected function getTablePage(): string
    {
        return ListProducts::class;
    }

    /**
     * Return array of stats to display
     */
    protected function getStats(): array
    {
        $query = $this->getPageTableQuery();

        return [
            // Total number of unique products
            Stat::make('Product Catalog Size', $query->count())
                ->chart([27, 27])
                ->color('info'),

            // Total inventory stock (sum of all product stock values)
            Stat::make('Product Stocks', $query->sum('stock'))
                ->chart([27, 27])
                ->color('info'),

            // Average price, formatted as USD with 2 decimals
            /* Stat::make('Average Price', '$ ' . number_format($query->avg('price'), 2))
                ->chart([27, 27])
                ->color('info'), */
            Stat::make('Stock Value', function () use ($query) {
                // Eloquent sum using a callback, avoids GROUP BY issues
                $value = $query->get()->sum(fn($product) => $product->stock * $product->price);
                return '$' . number_format($value, 2);
            })
                ->chart([27, 27])
                ->color('info'),
        ];
    }
}
