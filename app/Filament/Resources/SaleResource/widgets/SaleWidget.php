<?php

namespace App\Filament\Resources\SaleResource\Widgets;

use App\Filament\Resources\SaleResource\Pages\ListSales;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SaleWidget extends BaseWidget
{
    use InteractsWithPageTable;

    // No automatic polling (refresh)
    protected static ?string $pollingInterval = null;

    // Connect this widget to the ListSales page table
    protected function getTablePage(): string
    {
        return ListSales::class;
    }

    // Set the number of columns in the widget layout
    protected int | array $columns = 3;

    protected function getStats(): array
    {
        // Dates for current and previous periods
        $today = Carbon::today();

        $startOfThisMonth = now()->startOfMonth();
        $startOfLastMonth = now()->subMonth()->startOfMonth();
        $endOfLastMonth = now()->subMonth()->endOfMonth();

        $startOfThisYear = now()->startOfYear();
        $startOfLastYear = now()->subYear()->startOfYear();
        $endOfLastYear = now()->subYear()->endOfYear();

        $startOfYear = now()->startOfYear();
        $endOfYear = now()->endOfYear();

        // Get total sales count
        $totalSales = DB::table('sales')->count();

        // Get today's sales count
        $todaysSales = DB::table('sales')->whereDate('created_at', $today)->count();

        // Sum of all sales revenue (total_pay)
        $saleTotal = DB::table('sales')->sum('total_pay');

        // Sum of all imports (cost of goods)
        $importTotal = DB::table('product_import_items')
            ->join('product_imports', 'product_import_items.product_import_id', '=', 'product_imports.id')
            ->selectRaw('SUM(qty * unit_price) as total')
            ->value('total') ?? 0;

        // Imports for current year
        $thisYearImportTotal = DB::table('product_import_items')
            ->join('product_imports', 'product_import_items.product_import_id', '=', 'product_imports.id')
            ->whereBetween('product_imports.import_date', [$startOfYear, $endOfYear])
            ->selectRaw('SUM(qty * unit_price) as total')
            ->value('total') ?? 0;

        // Total quantity of items sold
        $itemsold = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->selectRaw('SUM(qty) as total')
            ->value('total') ?? 0;

        // Calculate total profit (revenue - cost)
        $profit = $saleTotal - $importTotal;

        // Sales counts for this month and last month
        $thisMonthSale = DB::table('sales')->whereBetween('sale_date', [$startOfThisMonth, now()])->count();
        $lastMonthSale = DB::table('sales')->whereBetween('sale_date', [$startOfLastMonth, $endOfLastMonth])->count();

        // Sales counts for this year and last year
        $thisYearSale = DB::table('sales')->whereBetween('sale_date', [$startOfThisYear, now()])->count();
        $lastYearSale = DB::table('sales')->whereBetween('sale_date', [$startOfLastYear, $endOfLastYear])->count();

        // Revenue for this month and last month
        $thisMonthRevenue = DB::table('sales')->whereBetween('sale_date', [$startOfThisMonth, now()])->sum('total_pay');
        $lastMonthRevenue = DB::table('sales')->whereBetween('sale_date', [$startOfLastMonth, $endOfLastMonth])->sum('total_pay');

        // Revenue for this year and last year
        $thisYearRevenue = DB::table('sales')->whereBetween('sale_date', [$startOfThisYear, now()])->sum('total_pay');
        $lastYearRevenue = DB::table('sales')->whereBetween('sale_date', [$startOfLastYear, $endOfLastYear])->sum('total_pay');

        // Calculate differences for month and year comparisons
        $diffMonthRevenue = $thisMonthRevenue - $lastMonthRevenue;
        $diffYearRevenue = $thisYearRevenue - $lastYearRevenue;
        $diffMonthSale = $thisMonthSale - $lastMonthSale;
        $diffYearSale = $thisYearSale - $lastYearSale;

        // Prepare descriptions and colors for monthly revenue difference
        $monthRevenueDescription = 'No data from last month';
        $monthRevenueColor = 'gray';

        if ($lastMonthRevenue > 0) {
            $prefix = $diffMonthRevenue >= 0 ? '+' : '-';
            $percent = number_format(abs($diffMonthRevenue / $lastMonthRevenue * 100), 1);
            $amount = number_format(abs($diffMonthRevenue), 2);
            $monthRevenueDescription = "{$prefix}$$amount  ({$percent}%) than last month";
            $monthRevenueColor = $diffMonthRevenue >= 0 ? 'success' : 'danger';
        }

        // Prepare descriptions and colors for yearly revenue difference
        $yearRevenueDescription = 'No data from last year';
        $yearRevenueColor = 'gray';

        if ($lastYearRevenue > 0) {
            $prefix = $diffYearRevenue >= 0 ? '+' : '-';
            $percent = number_format(abs($diffYearRevenue / $lastYearRevenue * 100), 1);
            $amount = number_format(abs($diffYearRevenue), 2);
            $yearRevenueDescription = "{$prefix}$$amount  ({$percent}%) than last year";
            $yearRevenueColor = $diffYearRevenue >= 0 ? 'success' : 'danger';
        }

        // Prepare descriptions and colors for monthly sales difference
        $monthSaleDescription = 'No data from last month';
        $monthSaleColor = 'gray';

        if ($lastMonthSale > 0) {
            $amount = number_format(abs($diffMonthSale), 0);
            $monthSaleDescription = $diffMonthSale >= 0 ? "$amount more sales than last month" : "Need $amount sales to even";
            $monthSaleColor = $diffMonthSale >= 0 ? 'success' : 'danger';
        }

        // Prepare descriptions and colors for yearly sales difference
        $yearSaleDescription = 'No data from last year';
        $yearSaleColor = 'gray';

        if ($lastYearSale > 0) {
            $amount = number_format(abs($diffYearSale), 0);
            $yearSaleDescription = $diffYearSale >= 0 ? "$amount more sales than last year" : "Need $amount sales to even";
            $yearSaleColor = $diffYearSale >= 0 ? 'success' : 'danger';
        }

        // Calculate profit for this year
        $thisYearProfit = $thisYearRevenue - $thisYearImportTotal;

        // Return the stats array for display in widget
        return [
            // Total stats row
            Stat::make('Total Sales', $this->getPageTableQuery()->count())
                ->chart([27, 27])
                ->color('info'),

            Stat::make('Total Revenue', '$' . number_format($saleTotal, 2))
                ->chart([27, 27])
                ->color('info'),

            Stat::make('Total Quantity Sold', $itemsold)
                ->chart([27, 27])
                ->color('info'),

            // Monthly stats row
            Stat::make('This Month Sales', $thisMonthSale)
                ->chart([27, 27])
                ->color($monthSaleColor)
                ->description($monthSaleDescription),

            Stat::make('This Month Revenue', '$' . number_format($thisMonthRevenue, 2))
                ->color($monthRevenueColor)
                ->icon('heroicon-o-currency-dollar')
                ->descriptionIcon($diffMonthRevenue >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart([27, 27])
                ->description($monthRevenueDescription),

            Stat::make('Total Profit', '$' . number_format($profit, 2))
                ->color($profit >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-currency-dollar')
                ->chart([27, 27]),

            // Yearly stats row
            Stat::make('This Year Sale', $thisYearSale)
                ->chart([27, 27])
                ->color($yearSaleColor)
                ->description($yearSaleDescription),

            Stat::make('This Year Revenue', '$' . number_format($thisYearRevenue, 2))
                ->color($yearRevenueColor)
                ->icon('heroicon-o-currency-dollar')
                ->descriptionIcon($diffYearRevenue >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart([27, 27])
                ->description($yearRevenueDescription),

            Stat::make('This Year Profit', '$' . number_format($thisYearProfit, 2))
                ->chart([27, 27])
                ->color($thisYearProfit >= 0 ? 'success' : 'danger')
                ->description('compare to this year import'),
        ];
    }
}
