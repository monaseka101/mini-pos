<?php

namespace App\Filament\Resources\ProductResource\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class Brandbarcount extends ChartWidget
{
    use InteractsWithPageFilters;

    // Widget heading and layout configuration
    protected static ?string $heading = 'Product in Stocks by Brand';
    protected static ?string $height = '275px';
    protected static ?int $sort = 1;

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y', // Display horizontal bar chart
            'ticks' => [
                'precision' => 0, // Disable decimal points
            ],
            'plugins' => [
                'legend' => [
                    'display' => false, // Hide legend (since label is not dynamic)
                ],
            ],
        ];
    }

    protected function getData(): array
    {
        // Query top 8 brands by total product stock
        $data = DB::table('products')
            ->join('brands', 'products.brand_id', '=', 'brands.id')
            ->select('brands.name as brand')
            ->selectRaw('SUM(products.stock) as total_qty')
            ->groupBy('brands.name')
            ->orderByDesc('total_qty')
            ->limit(8)
            ->get();

        return [
            // Bar labels (brand names)
            'labels' => $data->pluck('brand')->toArray(),
            'datasets' => [
                [
                    // Stock data for each brand
                    'data' => $data->pluck('total_qty')->toArray(),

                    'borderColor' => 'rgba(255, 255, 255, 0.82)',
                    'backgroundColor' => [
                        '#e61010', // red
                        '#f5d716', // yellow
                        '#61f024', // green
                        '#1d16f0', // blue
                        '#e80ecb', // pink/purple
                        '#04ded3', // cyan
                        '#de9009', // orange
                        '#6208c9', // purple
                        '#f299c4', // light pink
                        '#fafafa', // white
                    ],
                ],
            ],
        ];
    }
}
