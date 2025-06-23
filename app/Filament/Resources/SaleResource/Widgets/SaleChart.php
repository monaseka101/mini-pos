<?php

namespace App\Filament\Resources\SaleResource\Widgets;

use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class SaleChart extends ChartWidget
{
    protected static ?string $heading = 'Number of sales per month';

    // protected static ?string $pollingInterval = '2s';

    protected static ?int $sort = 1;

    public ?string $filter = 'today';

    protected function getData(): array
    {
        $activateFilter = $this->filter;

        $data = Trend::model(Sale::class)
            ->dateColumn('sale_date')
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Sales',
                    'data' => $data->map(fn(TrendValue $value) => $value->aggregate),
                    'fill' => 'start',

                ],
            ],
            'labels' => $data->map(
                fn(TrendValue $value) => Carbon::parse($value->date)->format('M Y')
            ),
        ];
    }

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'week' => 'Last week',
            'month' => 'Last month',
            'year' => 'This year',
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
