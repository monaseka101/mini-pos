<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SaleStats extends BaseWidget
{
    use InteractsWithPageFilters; // sync with Dashboard filters

    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? now()->startOfMonth();
        $endDate   = $this->filters['endDate'] ?? now();

        $salesCount = DB::table('sales')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->count();

        $revenue = DB::table('sales')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->sum('total_pay');

        $importCost = DB::table('product_import_items')
            ->join('product_imports', 'product_import_items.product_import_id', '=', 'product_imports.id')
            ->whereBetween('product_imports.import_date', [$startDate, $endDate])
            ->selectRaw('SUM(qty * unit_price) as total')
            ->value('total') ?? 0;

        return [
            // Sales Count → link to new SalePage
            Stat::make('Sales Count', number_format($salesCount))

                ->url(route('filament.admin.pages.sale-page', [
                    'startDate' => Carbon::parse($startDate)->toDateString(),
                    'endDate'   => Carbon::parse($endDate)->toDateString(),
                ]))
                ->openUrlInNewTab(),

            // Revenue → optional link to Sales resource
            Stat::make('Revenue', '$' . number_format($revenue, 2))
                ->url(route('filament.admin.pages.sale-page', [
                    'startDate' => Carbon::parse($startDate)->toDateString(),
                    'endDate'   => Carbon::parse($endDate)->toDateString(),
                ]))
                ->openUrlInNewTab()
                ->icon('heroicon-o-currency-dollar')
                ->color('success'),

            // Import Cost → optional link to Product Imports resource
            Stat::make('Import Cost', '$' . number_format($importCost, 2))
                ->url(route('filament.admin.pages.import-page', [
                    'startDate' => Carbon::parse($startDate)->toDateString(),
                    'endDate'   => Carbon::parse($endDate)->toDateString(),
                ]))
                ->icon('heroicon-o-currency-dollar')
                ->color('danger'),
        ];
    }
}
