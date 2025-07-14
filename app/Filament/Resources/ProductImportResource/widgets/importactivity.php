<?php

namespace App\Filament\Resources\ProductImportResource\Widgets;

use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use App\Models\ProductImport;

class Productline extends ChartWidget
{

    protected static ?string $heading = 'Inventory Movement (Last 30 Days)';

    protected function getType(): string
    {
        return 'line';
    }


    protected function getData(): array
    {
        // Define the start date (30 days ago from today)
        $startDate = now()->subDays(30);

        // Use Flowframe Trend package to build trend data for the last 30 days
        $data = Trend::query(
            ProductImport::query()
                ->join('product_import_items', 'product_imports.id', '=', 'product_import_items.product_import_id')
        )
            ->dateColumn('product_imports.import_date')
            ->between(start: $startDate, end: now())     // Limit to the last 30 days
            ->perDay()                                   // Group data per day
            ->sum('product_import_items.qty');

        return [
            'datasets' => [
                [   // chart design
                    'label' => 'Imported Stock',
                    'data' => $data->map(fn($value) => $value->aggregate),
                    'tension' => 0.4,
                    'fill' => true,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgba(28, 244, 49, 0.82)',
                    'pointBackgroundColor' => 'rgba(28, 244, 49, 0.82)',
                    'borderWidth' => 2,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                    'pointBorderColor' => 'rgba(28, 244, 49, 0.82)',
                ]
            ],
            'labels' => $data->map(fn($value) => date('M j', strtotime($value->date))), // Format x-axis dates
        ];
    }
}
