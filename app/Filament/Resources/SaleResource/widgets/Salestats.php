<?php

namespace App\Filament\Resources\SaleResource\Widgets;

use App\Filament\Resources\SaleResource\Pages\ListSales;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class Salestats extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListSales::class;
    }

    protected function getStats(): array
    {
        $saleTotal = DB::table('sales')
            ->selectRaw('SUM(total_pay) as total')
            ->value('total') ?? 0;

        $itemsold = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->selectRaw('SUM(qty) as total')
            ->value('total') ?? 0;
        return [
            Stat::make('Total Sales', $this->getPageTableQuery()->count())
                ->chart([27, 27])
                ->color('info'),

            Stat::make('Total Revenue', '$' . number_format($saleTotal, 2))
                ->chart([27, 27])
                ->color('info'),

            Stat::make('Total Quantity Sold', $itemsold)->chart([27, 27])
                ->color('info'),
        ];
    }
}
