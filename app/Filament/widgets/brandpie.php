<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use App\Models\SaleItem;
use Filament\Widgets\ChartWidget;


class Brandpie extends ChartWidget
{
    protected static ?string $heading = 'Top Brands (by Qty Sold) All time';
    protected static ?string $maxHeight = '275px';
    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'ticks' => [
                'precision' => 0,
            ],
        ];
    }


    protected function getData(): array
    {
        //$year = now()->year;

        $data = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('brands', 'products.brand_id', '=', 'brands.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            //->whereYear('sales.sale_date', $year)
            ->select('brands.name as brand', DB::raw('SUM(COALESCE(sale_items.qty, 0)) as total_qty'))
            ->groupBy('brands.name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        return [

            'labels' => $data->pluck('brand')->toArray(),
            'datasets' => [
                [
                    'data' => $data->pluck('total_qty')->toArray(),
                    //'borderColor' => 'rgba(42, 41, 41, 0.82)',
                    'borderColor' => 'rgba(255, 255, 255, 0.82)',
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
