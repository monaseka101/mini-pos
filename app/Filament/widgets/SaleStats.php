<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\SaleResource\Pages\ListSales;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SaleStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    /**
     * Links this widget to the ListSales page for table interactions
     */
    protected function getTablePage(): string
    {
        return ListSales::class;
    }

    /**
     * Returns the stats displayed in the widget
     */
    protected function getStats(): array
    {
        $today = Carbon::today();

        // === BASIC SALES STATS ===
        $totalSales = DB::table('sales')->count();
        $todaysSales = DB::table('sales')->whereDate('created_at', $today)->count();

        // === TOTAL PROFIT: revenue - import cost ===
        $saleTotal = DB::table('sales')
            ->selectRaw('SUM(total_pay) as total')
            ->value('total') ?? 0;

        $importTotal = DB::table('product_import_items')
            ->join('product_imports', 'product_import_items.product_import_id', '=', 'product_imports.id')
            ->selectRaw('SUM(qty * unit_price) as total')
            ->value('total') ?? 0;

        $profit = $saleTotal - $importTotal;

        // === MONTHLY COMPARISON SETUP ===
        $now = now();
        $startOfThisMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        // Sale count this vs. last month
        $thisMonthSaleCount = DB::table('sales')->whereBetween('sale_date', [$startOfThisMonth, $now])->count();
        $lastMonthSaleCount = DB::table('sales')->whereBetween('sale_date', [$startOfLastMonth, $endOfLastMonth])->count();

        // Revenue this vs. last month
        $thisMonthRevenue = DB::table('sales')
            ->whereBetween('sale_date', [$startOfThisMonth, $now])
            ->selectRaw('SUM(total_pay) as total')
            ->value('total') ?? 0;

        $lastMonthRevenue = DB::table('sales')
            ->whereBetween('sale_date', [$startOfLastMonth, $endOfLastMonth])
            ->selectRaw('SUM(total_pay) as total')
            ->value('total') ?? 0;

        // === REVENUE COMPARISON DESCRIPTION ===
        $revenueDiff = $thisMonthRevenue - $lastMonthRevenue;
        $description = 'No data from last month';
        $descriptionColor = 'gray';

        if ($lastMonthRevenue > 0) {
            $prefix = $revenueDiff >= 0 ? '+' : '-';
            $percent = number_format(abs($revenueDiff / $lastMonthRevenue * 100), 1);
            $amount = number_format(abs($revenueDiff), 2);
            $description = "{$prefix}$$amount ({$percent}%) than last month";
            $descriptionColor = $revenueDiff >= 0 ? 'success' : 'danger';
        }

        // === SALE COUNT COMPARISON DESCRIPTION ===
        $saleDiff = $thisMonthSaleCount - $lastMonthSaleCount;
        $descriptionsale = 'No data from last month';

        if ($lastMonthSaleCount > 0) {
            $amount = number_format(abs($saleDiff), 0);
            $descriptionsale = $saleDiff >= 0
                ? "$amount more sale than last month"
                : "Need $amount sale to even";
        }

        return [

            // Monthly Sales Stat Box
            Stat::make('This Month Sales', $thisMonthSaleCount)
                ->chart([27, 27])
                ->color($saleDiff >= 0 ? 'success' : 'danger')
                ->description($descriptionsale),

            // Profit Box
            Stat::make('Total Profit', '$' . number_format($profit, 2))
                ->color($profit >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-currency-dollar')
                ->chart([27, 27]),

            // Revenue Box
            Stat::make('This Month Revenue', '$' . number_format($thisMonthRevenue, 2))
                ->color($thisMonthRevenue >= $lastMonthRevenue ? 'success' : 'danger')
                ->icon('heroicon-o-currency-dollar')
                ->descriptionIcon($revenueDiff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart([27, 27])
                ->description($description)
                ->descriptionColor($descriptionColor),
        ];
    }
}
