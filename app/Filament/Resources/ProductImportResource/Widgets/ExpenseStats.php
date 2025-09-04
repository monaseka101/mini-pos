<?php

namespace App\Filament\Resources\ProductImportResource\Widgets;

use App\Filament\Resources\ProductImportResource;
use App\Models\ProductImport;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Sale;
use Carbon\Carbon;

class ExpenseStats extends BaseWidget
{

    protected function getStats(): array
    {
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->format('F');
        return [
            Stat::make('Today Expense', '$ ' . number_format(ProductImport::totalExpenseForToday(), 2))
                ->url(ProductImportResource::getUrl('index') . '?tableFilters[import_date][import_from]=' . now()->toDateString() . '&tableFilters[import_date][import_until]=' . now()->toDateString()),
            Stat::make("Total Expense this month ({$currentMonth})", '$ ' . number_format(ProductImport::totalExpenseThisMonth(), 2))
                ->url(ProductImportResource::getUrl('index') . '?tableFilters[import_date][import_from]=' . now()->startOfMonth()->toDateString() . '&tableFilters[import_date][import_until]=' . now()->endOfMonth()->toDateString()),
            Stat::make("Total Expense this year  ({$currentYear})", '$ ' . number_format(ProductImport::totalExpenseThisYear(), 2))
                ->url(ProductImportResource::getUrl('index') . '?tableFilters[import_date][import_from]=' . now()->startOfYear()->toDateString() . '&tableFilters[import_date][import_until]=' . now()->endOfYear()->toDateString())
        ];
    }
}
