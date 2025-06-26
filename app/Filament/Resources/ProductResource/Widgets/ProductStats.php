<?php

namespace App\Filament\Resources\ProductResource\Widgets;

use App\Filament\Resources\ProductResource\Pages\ListProducts;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProductStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListProducts::class;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Total Unique Products', $this->getPageTableQuery()->count())->chart([27, 27])
                ->color('info'),

            Stat::make('Product Inventory', $this->getPageTableQuery()->sum('stock'))->chart([27, 27])
                ->color('info'),
            Stat::make('Average Price', '$ ' . number_format($this->getPageTableQuery()->avg('price'), 2))->chart([27, 27])
                ->color('info'),
        ];
    }
}
