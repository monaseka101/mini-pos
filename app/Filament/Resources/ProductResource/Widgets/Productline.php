<?php

namespace App\Filament\Resources\ProductResource\Widgets;

use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use App\Models\ProductImport;
use App\Models\Sale;
// product import from the last 30 days
class Productline extends ChartWidget
{
    protected static ?string $heading = 'Product Imported and Sold (Last 30 Days)';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $startDate = now()->subDays(30); // Get data from the last 30 days

        // Trend data: daily imported quantities
        $importData = Trend::query(
            ProductImport::query()
                ->join('product_import_items', 'product_imports.id', '=', 'product_import_items.product_import_id')
        )
            ->dateColumn('product_imports.import_date')
            ->between(start: $startDate, end: now())
            ->perDay()
            ->sum('product_import_items.qty');

        // Trend data: daily sold quantities
        $saleData = Trend::query(
            Sale::query()
                ->join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
        )
            ->dateColumn('sales.sale_date')
            ->between(start: $startDate, end: now())
            ->perDay()
            ->sum('sale_items.qty');

        return [
            'datasets' => [
                [
                    'label' => 'Imported Stock',
                    'data' => $importData->pluck('aggregate')->toArray(),
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
                    'data' => $saleData->pluck('aggregate')->toArray(),
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
            'labels' => $importData->pluck('date')->map(fn($date) => date('M j', strtotime($date)))->toArray(),
        ];
    }
}
