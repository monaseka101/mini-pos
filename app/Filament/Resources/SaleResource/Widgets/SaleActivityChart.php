<?php

namespace App\Filament\Resources\SaleResource\Widgets;

use App\Models\Sale;
use App\Models\SaleItem;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;

class SaleActivityChart extends ChartWidget
{
    protected static ?string $heading = 'Sale activities over the last 30 days';

    protected function getData(): array
    {
        $startDate = now()->subDays(30);

        $data = Trend::query(
            Sale::query()
                ->join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
        )
            ->dateColumn('sale_date')
            ->between(
                start: $startDate,
                end: now()
            )
            ->perDay()
            ->sum('sale_items.qty * sale_items.unit_price');

        return [
            'datasets' => [
                [
                    'label' => 'Daily Sales',
                    'data' => $data->map(fn($value) => $value->aggregate),
                    'tension' => 0.4,
                    'fill' => true,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)', // Light blue background
                    // 'borderColor' => 'rgb(59, 130, 246)', // Blue border
                    // 'pointBackgroundColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                    'pointBorderColor' => '#fff',
                ]
            ],
            'labels' => $data->map(fn($value) => date('M j', strtotime($value->date))),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
