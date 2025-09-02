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
    public ?string $filter = '2025'; // Changed default to current year

    protected function getData(): array
    {
        // Get the selected year from filter, default to current year
        $selectedYear = $this->filter ?? now()->year;
        $data = Trend::query(
            ProductImport::query()
                ->join('product_import_items as PIT', 'product_imports.id', '=', 'PIT.product_import_id')
        )
            ->dateColumn('import_date')
            ->between(
                start: Carbon::createFromDate($selectedYear, 1, 1)->startOfYear(),
                end: Carbon::createFromDate($selectedYear, 12, 31)->endOfYear(),
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
