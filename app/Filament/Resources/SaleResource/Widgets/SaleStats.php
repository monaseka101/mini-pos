<?php
namespace App\Filament\Resources\SaleResource\Widgets;

use App\Filament\Resources\ProductResource;
use App\Filament\Resources\SaleResource;
use App\Models\Sale;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SaleStats extends BaseWidget
{
    protected function getStats(): array
    {
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->format('F');

        return [
            Stat::make('Today Sale', '$ ' . number_format(Sale::totalSaleForToday(), 2))
                ->url(SaleResource::getUrl('index') . '?tableFilters[sale_date][sale_from]=' . now()->toDateString() . '&tableFilters[sale_date][sale_until]=' . now()->toDateString()),

            Stat::make('Total Sale this month (' . $currentMonth . ')', '$ ' . number_format(Sale::totalSaleForThisMonth(), 2))
                ->url(SaleResource::getUrl('index') . '?tableFilters[sale_date][sale_from]=' . now()->startOfMonth()->toDateString() . '&tableFilters[sale_date][sale_until]=' . now()->endOfMonth()->toDateString()),

            Stat::make("Total Sale this year ({$currentYear})", '$ ' . number_format(Sale::totalSaleForThisYear(), 2))
                ->url(SaleResource::getUrl('index') . '?tableFilters[sale_date][sale_from]=' . now()->startOfYear()->toDateString() . '&tableFilters[sale_date][sale_until]=' . now()->endOfYear()->toDateString())
        ];
    }
}
