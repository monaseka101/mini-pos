<?php

namespace App\Filament\Resources\SaleResource\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class Categorybar extends ChartWidget
{
    protected static ?string $heading = 'Top Categories (by Revenue) All Time';
    protected static ?string $maxHeight = '275px';

    // Chart type is bar
    protected function getType(): string
    {
        return 'bar';
    }

    // Chart options - horizontal bar chart, no decimal ticks
    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'x',
            'ticks' => [
                'precision' => 0,
            ],
            'plugins' => [
                'legend' => [
                    'display' => false, // usually bar chart labels shown, so legend off
                ],
            ],
        ];
    }

    // Data query and structuring for chart
    protected function getData(): array
    {
        $data = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->leftJoin('discounts', 'sale_items.discount_id', '=', 'discounts.id')
            //->whereYear('sales.sale_date', now()->year) // Uncomment to filter current year only
            ->select('categories.name as category')
            ->selectRaw(
                "SUM(
                    sale_items.qty * sale_items.unit_price
                    - CASE 
                        WHEN discounts.ispercent = 1 THEN sale_items.qty * sale_items.unit_price * (discounts.value / 100)
                        ELSE sale_items.qty * discounts.value
                      END
                ) as total_revenue"
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_revenue')
            ->limit(8)
            ->get();

        return [
            'labels' => $data->pluck('category')->toArray(),
            'datasets' => [
                [
                    'data' => $data->pluck('total_revenue')->toArray(),
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
                    ],
                ],
            ],
        ];
    }
}
