<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use App\Models\SaleItem;
use Filament\Widgets\ChartWidget;


class Categorypie extends ChartWidget
{
    protected static ?string $heading = 'Top Categories (by Qty Sold)';
    protected static ?string $maxHeight = '300px';
    protected function getType(): string
    {
        return 'pie';
    }


    protected function getData(): array
    {
        $year = now()->year;

        $data = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereYear('sales.sale_date', $year)
            ->select('categories.name as category', DB::raw('SUM(sale_items.qty) as total_qty'))
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        return [

            'labels' => $data->pluck('category')->toArray(),
            'datasets' => [
                [
                    'data' => $data->pluck('total_qty')->toArray(),
                    'backgroundColor' => [
                        '#e61010', //red
                        '#f5d716', //yellow
                        '#61f024', //green
                        '#1d16f0', //blue
                        '#e80ecb', //purple,pink
                        '#04ded3', //cyan
                        '#de9009', //ornage
                        '#6208c9', //purple
                        '#f299c4', //pink
                        '#fafafa' //white
                    ],
                ],
            ],
        ];
    }
}
