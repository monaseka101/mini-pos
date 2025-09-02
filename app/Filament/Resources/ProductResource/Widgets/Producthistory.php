<?php

namespace App\Filament\Resources\ProductResource\Widgets;

use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use App\Models\ProductImport;
use App\Models\Sale;

//Product History of Last 2 years
class Producthistory extends ChartWidget
{
    protected static ?string $heading = 'Product Imported and Sold (Last 2 Years)';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $startDate = now()->subYears(2); // Look back 2 years

        // Get monthly imported quantity trends
        $importData = Trend::query(
            ProductImport::query()
                ->join('product_import_items', 'product_imports.id', '=', 'product_import_items.product_import_id')
        )
            ->dateColumn('product_imports.import_date')
            ->between(start: $startDate, end: now())
            ->perMonth() // Group by month
            ->sum('product_import_items.qty');

        // Get monthly sold quantity trends
        $saleData = Trend::query(
            Sale::query()
                ->join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
        )
            ->dateColumn('sales.sale_date')
            ->between(start: $startDate, end: now())
            ->perMonth()
            ->sum('sale_items.qty');

        return [
            'datasets' => [
                [
                    'label' => 'Imported Stock',
                    'data' => $importData->map(fn($value) => $value->aggregate), // total imported per month
                    'tension' => 0.4,
                    'fill' => true,
                    'backgroundColor' => 'rgba(248, 72, 72, 0.08)',
                    'borderColor' => 'rgba(220, 38, 38, 0.82)',
                    'pointBackgroundColor' => 'rgba(220, 38, 38, 0.82)',
                    'pointBorderColor' => 'rgba(220, 38, 38, 0.82)',
                    'borderWidth' => 2,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                ],
                [
                    'label' => 'Sold Quantity',
                    'data' => $saleData->map(fn($value) => $value->aggregate), // total sold per month
                    'tension' => 0.4,
                    'fill' => true,
                    'backgroundColor' => 'rgba(59, 131, 246, 0.07)',
                    'borderColor' => 'rgba(12, 136, 238, 0.82)',
                    'pointBackgroundColor' => 'rgba(12, 136, 238, 0.82)',
                    'pointBorderColor' => 'rgba(12, 136, 238, 0.82)',
                    'borderWidth' => 2,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                ],
            ],
            // Use month-year format for X-axis labels
            'labels' => $importData->map(fn($value) => date('M Y', strtotime($value->date))),
        ];
    }
}
