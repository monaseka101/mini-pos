<?php

namespace App\Filament\Resources\SaleResource\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class Brandbar extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Top Brands (by Revenue) All Time';

    protected static ?string $maxHeight = '275px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'x', // Horizontal bar chart (default)
            'ticks' => [
                'precision' => 0, // No decimal places on ticks
            ],
            'plugins' => [
                'legend' => [
                    'display' => false, // Hide legend as only one dataset
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => "function(context) {
                            return context.parsed.y.toLocaleString(undefined, { style: 'currency', currency: 'USD' });
                        }",
                    ],
                ],
            ],
        ];
    }

    /**
     * Query and prepare the data for the chart
     */
    protected function getData(): array
    {
        $data = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('brands', 'products.brand_id', '=', 'brands.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->leftJoin('discounts', 'sale_items.discount_id', '=', 'discounts.id')
            ->select('brands.name as brand')
            ->selectRaw(
                "SUM(
                    sale_items.qty * sale_items.unit_price
                    - CASE 
                        WHEN discounts.ispercent = 1 THEN sale_items.qty * sale_items.unit_price * (discounts.value / 100)
                        ELSE sale_items.qty * discounts.value
                    END
                ) as total_revenue"
            )
            ->groupBy('brands.name')
            ->orderByDesc('total_revenue')
            ->limit(8)
            ->get();

        return [
            'labels' => $data->pluck('brand')->toArray(),
            'datasets' => [
                [
                    'data' => $data->pluck('total_revenue')->toArray(),
                    'borderColor' => 'rgba(255, 255, 255, 0.82)',
                    'backgroundColor' => [
                        '#e61010', // red
                        '#f5d716', // yellow
                        '#61f024', // green
                        '#1d16f0', // blue
                        '#e80ecb', // purple/pink
                        '#04ded3', // cyan
                        '#de9009', // orange
                        '#6208c9', // purple
                    ],
                ],
            ],
        ];
    }
}
