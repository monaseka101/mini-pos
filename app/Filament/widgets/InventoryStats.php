<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use App\Models\Product;

class InventoryStats extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Active Products', Product::where('active', true)->count())
                ->chart([17, 17])
                ->color('info')
                ->description(' '),

            Stat::make('Product Stocks', Product::sum('stock'))
                ->chart([27, 27])
                ->color('info'),

            Stat::make('Total Stocks Value', function (): string {
                $value = Product::select(DB::raw('SUM(stock * price) as total_value'))
                    ->value('total_value');

                return '~ $' . number_format($value ?? 0, 2); // format to 2 decimal places
            })->chart([27, 27])
                ->color('info'),
        ];
    }
}
