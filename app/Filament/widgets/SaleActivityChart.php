<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;

class SaleActivityChart extends ChartWidget
{
    protected static ?string $heading = 'Sale activities over the last 30 days';

    protected function getOptions(): array
    {
        return [
            'ticks' => [
                'precision' => 0,
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
        $startDate = now()->subDays(29)->startOfDay();


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
                    'borderColor' => '#22c55e', // Blue border
                    'pointBackgroundColor' => '#22c55e',
                    'borderWidth' => 2,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                    'pointBorderColor' => '#22c55e',
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
