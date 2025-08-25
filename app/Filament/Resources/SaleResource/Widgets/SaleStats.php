<?php

namespace App\Filament\Resources\SaleResource\Widgets;

use App\Models\Sale;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SaleStats extends BaseWidget
{
    protected function getStats(): array
    {
        $today = Carbon::now();
        $currentMonth = Carbon::now()->format('F');
        return [
            Stat::make('Today Sale', '$ ' . number_format(Sale::totalSaleForToday(), 2)),
            Stat::make('Total Sale this month (' . $currentMonth . ')', '$ ' . number_format(Sale::totalSaleForThisMonth(), 2)),
            Stat::make('Total Sale this year', '$ ' . number_format(Sale::totalSaleForThisYear(), 2)),
        ];
    }
}
