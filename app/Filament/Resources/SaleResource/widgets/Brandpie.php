<?php

namespace App\Filament\Resources\SaleResource\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class Brandpie extends ChartWidget
{
    use InteractsWithPageFilters;

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
                'precision' => 0, // no decimal ticks
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'right',
                ],
            ],
        ];
    }
    /**
     * Query data according to filter mode (qty or price)
     */
    protected function getData(): array
    {
        $mode = $this->filters['mode'] ?? 'qty';

        $data = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('brands', 'products.brand_id', '=', 'brands.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->leftJoin('discounts', 'sale_items.discount_id', '=', 'discounts.id')
            ->select('brands.name as brand')
            ->selectRaw(
                $mode === 'price'
                    ? "SUM(
                        sale_items.qty * sale_items.unit_price
                        - CASE 
                            WHEN discounts.ispercent = 1 THEN sale_items.qty * sale_items.unit_price * (discounts.value / 100)
                            ELSE sale_items.qty * discounts.value
                          END
                      ) as total_qty"
                    : "SUM(sale_items.qty) as total_qty"
            )
            ->groupBy('brands.name')
            ->orderByDesc('total_qty')
            ->limit(8)
            ->get();

        return [
            'labels' => $data->pluck('brand')->toArray(),
            'datasets' => [
                [
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
                    ],
                ],
            ],
        ];
    }
}
