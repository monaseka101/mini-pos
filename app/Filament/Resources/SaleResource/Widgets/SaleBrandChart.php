<?php

namespace App\Filament\Resources\SaleResource\Widgets;

use Filament\Widgets\ChartWidget;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Facades\DB;

class SaleBrandChart extends ChartWidget
{
    protected static ?string $heading = 'Most Selling Brands';

    protected function getData(): array
    {
        $year = now()->year; // Change this to the desired year

        $data = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('brands', 'products.brand_id', '=', 'brands.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereYear('sales.sale_date', $year)
            ->select('brands.name as brand', DB::raw('SUM(sale_items.qty) as total_qty'))
            ->groupBy('brands.id', 'brands.name')
            ->orderByDesc('total_qty')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Sales by Brand',
                    'data' => $data->pluck('total_qty')->toArray(),
                    'backgroundColor' => [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF',
                        '#FF9F40',
                        '#2ECC71',
                        '#E74C3C',
                        '#3498DB',
                        '#F1C40F',
                    ],
                ],
            ],
            'labels' => $data->pluck('brand')->toArray(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => true,
            'aspectRatio' => 2, // This makes the chart wider, similar to a line chart
        ];
    }

    // protected function getFilters(): ?array
    // {
    //     return [
    //         'brand' => 'Brand',
    //         'Category' => 'Category',
    //     ];
    // }

    protected function getType(): string
    {
        return 'pie';
    }
}
