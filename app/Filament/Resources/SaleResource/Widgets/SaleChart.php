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
    protected static ?string $heading = 'Sales Per Month Within a year.';
    // protected static ?string $pollingInterval = '2s';
    protected static ?int $sort = 1;

    public ?string $filter = '2025'; // Changed default to current year

    protected function getData(): array
    {
        // Get the selected year from filter, default to current year
        $selectedYear = $this->filter ?? now()->year;

        $data = Trend::query(
            Sale::query()
                ->join('sale_items as SI', 'sales.id', '=', 'SI.sale_id')
        )
            ->dateColumn('sale_date')
            ->between(
                start: Carbon::createFromDate($selectedYear, 1, 1)->startOfYear(),
                end: Carbon::createFromDate($selectedYear, 12, 31)->endOfYear(),
            )
            ->perMonth()
            ->sum('SI.unit_price * SI.qty * (1 - COALESCE(SI.discount, 0)/100)');

        return [
            'datasets' => [
                [
                    'label' => 'Sale Revenue',
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

    protected function getFilters(): ?array
    {
        // Generate a dynamic list of years (current year and previous years)
        $currentYear = now()->year;
        $years = [];

        // Add current year and previous 4 years
        for ($i = 0; $i < 5; $i++) {
            $year = $currentYear - $i;
            $years[(string) $year] = (string) $year;
        }

        return $years;
    }

    protected function getType(): string
    {
        return 'line';
    }
}
