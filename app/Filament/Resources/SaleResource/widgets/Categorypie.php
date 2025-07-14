<?php

namespace App\Filament\Resources\SaleResource\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class Categorypie extends ChartWidget
{
    protected static ?string $heading = 'Top Categories (by Qty Sold) All Time';
    protected static ?string $maxHeight = '275px';

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'ticks' => [
                'precision' => 0, // no decimal ticks
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'right',
                ],
            ],
        ];
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
            ->limit(8)
            ->get();

        return [
            'labels' => $data->pluck('category')->toArray(),
            'datasets' => [[
                'data' => $data->pluck('total_qty')->toArray(),
                'borderColor' => 'rgba(255, 255, 255, 0.82)',
                'backgroundColor' => [
                    '#e61010',
                    '#f5d716',
                    '#61f024',
                    '#1d16f0',
                    '#e80ecb',
                    '#04ded3',
                    '#de9009',
                    '#6208c9',
                    '#f299c4',
                    '#fafafa',
                ],
            ]],
        ];
    }
}
