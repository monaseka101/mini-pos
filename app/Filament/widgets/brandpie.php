<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

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
            'ticks' => [
                'precision' => 0,
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'right',
                ],
            ],
        ];
    }

    /**
     * Fetch and format data for the chart
     */
    protected function getData(): array
    {
        // Get top 10 selling brands by quantity sold (all time)
        $data = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('brands', 'products.brand_id', '=', 'brands.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->select('brands.name as brand', DB::raw('SUM(COALESCE(sale_items.qty, 0)) as total_qty'))
            ->groupBy('brands.name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();
        $total = $data->sum('total_qty');

        return [
            //'labels' => $data->pluck('brand')->toArray(), // Brand names
            'labels' => $data->map(function ($row) use ($total) {
                $percent = round(($row->total_qty / $total) * 100);
                return "{$row->brand} ({$percent}%)";
            })->toArray(),
            'datasets' => [
                [
                    'data' => $data->pluck('total_qty')->toArray(), // Quantity sold per brand
                    'borderColor' => 'rgba(255, 255, 255, 0.82)', // White border for clarity
                    'backgroundColor' => [
                        '#e61010', // Red
                        '#f5d716', // Yellow
                        '#61f024', // Green
                        '#1d16f0', // Blue
                        '#e80ecb', // Purple-pink
                        '#04ded3', // Cyan
                        '#de9009', // Orange
                        '#6208c9', // Dark Purple
                        '#f299c4', // Light Pink
                        '#fafafa', // White
                    ],
                ],
            ],
        ];
    }
}
