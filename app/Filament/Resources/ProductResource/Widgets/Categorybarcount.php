<?php

namespace App\Filament\Resources\ProductResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use App\Models\ProductItem;
use Filament\Widgets\ChartWidget;
use App\Models\Product;
use Filament\Forms\Components\Select;
use Filament\Widgets\Concerns\InteractsWithPageFilters;


class Categorybarcount extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Product Stocks by Category';
    protected static ?string $height = '275px';
    protected static ?int $sort = 1;
    protected function getType(): string
    {
        return 'bar';
    }
    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'ticks' => [
                'precision' => 0,
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
        ];
    }

    protected function getData(): array
    {
        $data = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.name as category')
            ->selectRaw('SUM(products.stock) as total_qty')
            ->groupBy('categories.name')
            ->orderByDesc('total_qty')
            ->limit(8)
            ->get();

        return [

            'labels' => $data->pluck('category')->toArray(),
            'datasets' => [
                [

                    'data' => $data->pluck('total_qty')->toArray(),

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
