<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class ProductImportChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Import Chart';

    protected function getData(): array
    {
        // Access dashboard filters
        $startDate = $this->filters['startDate'] ?? now()->startOfMonth();
        $endDate = $this->filters['endDate'] ?? now();

        $start = \Carbon\Carbon::parse($startDate)->startOfMonth();
        $end = \Carbon\Carbon::parse($endDate)->endOfMonth();

        // Fetch monthly totals
        $imports = DB::table('product_import_items')
            ->join('product_imports', 'product_import_items.product_import_id', '=', 'product_imports.id')
            ->selectRaw('DATE_FORMAT(product_imports.import_date, "%Y-%m") as month, SUM(product_import_items.qty * product_import_items.unit_price) as total')
            ->whereBetween('product_imports.import_date', [$start, $end])
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $months = array_keys($imports);
        $totals = array_values($imports);
        $count = count($totals);

        // Downsample if too many points (e.g., > 40 months)
        if ($count > 40) {
            $step = ($count - 1) / 39;
            $newTotals = [];
            $newMonths = [];
            for ($i = 0; $i < 39; $i++) {
                $index = (int) floor($i * $step);
                $newTotals[] = $totals[$index];
                $newMonths[] = $months[$index];
            }
            // Always include the last point
            $newTotals[] = $totals[$count - 1];
            $newMonths[] = $months[$count - 1];

            $totals = $newTotals;
            $months = $newMonths;
        }

        return [
            'labels' => $months,
            'datasets' => [
                [
                    'label' => 'Imports',
                    'data' => $totals,
                    'fill' => true,
                    'borderColor' => '#f44336',
                    'backgroundColor' => '#f4433633',
                    'pointBackgroundColor' => 'rgba(220, 38, 38, 0.82)',
                    'borderWidth' => 2,
                    'pointRadius' => 3,
                    'pointHoverRadius' => 6,
                    'tension' => 0.4,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
