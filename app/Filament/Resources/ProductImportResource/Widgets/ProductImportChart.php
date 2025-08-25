<?php

namespace App\Filament\Resources\ProductImportResource\Widgets;

use App\Models\ProductImport;
use App\Models\Sale;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class ProductImportChart extends ChartWidget
{
    protected static ?string $heading = 'Product imports per month in a year';

    protected function getData(): array
    {
        $data = Trend::query(
            ProductImport::query()
                ->join('product_import_items as PIT', 'product_imports.id', '=', 'PIT.product_import_id')
        )
            ->dateColumn('import_date')
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->sum('PIT.qty * PIT.unit_price');

        return [
            'datasets' => [
                [
                    'label' => 'Product Import Value',
                    'data' => $data->map(fn(TrendValue $value) => $value->aggregate),
                    'tension' => 0.4,
                    'fill' => true,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    // 'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'pointBorderColor' => '#fff',
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                ],
            ],
            'labels' => $data->map(
                fn(TrendValue $value) => Carbon::parse($value->date)->format('M Y')
            ),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
