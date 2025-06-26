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

    protected function getTablePage(): string
    {
        return ListSales::class;
    }

    protected function getStats(): array
    {
        $today = Carbon::today();

        // Sales counts
        $totalSales = DB::table('sales')->count();
        $todaysSales = DB::table('sales')->whereDate('created_at', $today)->count();


        // Revenue: total sale - total import
        $saleTotal = DB::table('sales')
            ->selectRaw('SUM(total_pay) as total')
            ->value('total') ?? 0;

        $importTotal = DB::table('product_import_items')
            ->join('product_imports', 'product_import_items.product_import_id', '=', 'product_imports.id')
            ->selectRaw('SUM(qty * unit_price) as total')
            ->value('total') ?? 0;


        $Profit = $saleTotal - $importTotal;

        // Monthly revenue
        $startOfThisMonth = now()->startOfMonth();
        $startOfLastMonth = now()->subMonth()->startOfMonth();
        $endOfLastMonth = now()->subMonth()->endOfMonth();

        $thismonthSale = DB::table('sales')->whereBetween('sale_date', [$startOfThisMonth, now()])->count();
        $lastmonthSale = DB::table('sales')->whereBetween('sale_date', [$startOfLastMonth, $endOfLastMonth])->count();

        $thisMonthRevenue = DB::table('sales')
            ->whereBetween('sale_date', [$startOfThisMonth, now()])
            ->selectRaw('SUM(total_pay) as total')
            ->value('total') ?? 0;

        $lastMonthRevenue = DB::table('sales')

            ->whereBetween('sale_date', [$startOfLastMonth, $endOfLastMonth])
            ->selectRaw('SUM(total_pay) as total')
            ->value('total') ?? 0;

        $diff = $thisMonthRevenue - $lastMonthRevenue;
        $diffsale = $thismonthSale - $lastmonthSale;
        $description = 'No data from last month';
        $descriptionColor = 'gray';
        if ($lastMonthRevenue > 0) {
            $prefix = $diff >= 0 ? '+' : '-';
            $percent = number_format(abs($diff / $lastMonthRevenue * 100), 1);
            $amount = number_format(abs($diff), 2);
            $description = "{$prefix}$$amount  ({$percent}%)  from last month";
            $descriptionColor = $diff >= 0 ? 'success' : 'danger';
        }
        if ($lastmonthSale > 0) {
            $amount = number_format(abs($diffsale), 0);
            $descriptionsale = $diffsale >= 0 ? " $amount  more sale from last month" : "need $amount sale to even";
            $descriptionColor = $diffsale >= 0 ? 'success' : 'danger';
        }

        return [
            Stat::make('This month Sales', $thismonthSale)
                ->chart([27, 27])
                ->color($thismonthSale >= 0 ? 'success' : 'danger')
                ->description($descriptionsale),


            Stat::make('Total Profit', '$' . number_format($Profit, 2))
                ->color($Profit >= 0 ? 'success,' : 'danger')
                ->icon('heroicon-o-currency-dollar')
                ->chart([27, 27]),

            Stat::make('This Month Revenue', '$' . number_format($thisMonthRevenue, 2))
                ->color($thisMonthRevenue >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-currency-dollar')
                ->descriptionIcon($diff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart([27, 27])
                ->description($description)
                ->descriptionColor($descriptionColor),

        ];
    }
}
