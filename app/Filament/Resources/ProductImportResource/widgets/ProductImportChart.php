<?php

namespace App\Filament\Resources\ProductImportResource\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ProductImportChart extends ChartWidget
{
    // Chart title
    protected static ?string $heading = 'Monthly Import Chart';

    // Optional filter value from dropdown (holds selected year)
    public ?string $filter = null;

    /**
     * Define available filters (last 5 years including current)
     */
    protected function getFilters(): ?array
    {
        $currentYear = now()->year;

        return collect(range($currentYear, $currentYear - 5))
            ->mapWithKeys(fn($year) => [$year => (string) $year])
            ->toArray();
    }

    /**
     * Fetch and format chart data
     */
    protected function getData(): array
    {
        $year = $this->filter ?? now()->year;       // Current selected year
        $lastYear = $year - 1;                      // One year before
        $last2Year = $year - 2;                     // Two years before
        $now = now();

        // Closure to fetch monthly import totals for a given year
        $getMonthlyTotals = function (int $targetYear) use ($now): array {
            $query = DB::table('product_import_items')
                ->join('product_imports', 'product_import_items.product_import_id', '=', 'product_imports.id')
                ->selectRaw('MONTH(product_imports.import_date) as month, SUM(product_import_items.qty * product_import_items.unit_price) as total')
                ->whereYear('product_imports.import_date', $targetYear);

            // Limit data up to current month if selected year is current
            if ($targetYear === $now->year) {
                $query->whereMonth('product_imports.import_date', '<=', $now->month);
                $maxMonth = /* $now->month; */ 12;
            } else {
                $maxMonth = 12;
            }

            // Map month => total
            $monthlyTotals = $query->groupBy('month')->pluck('total', 'month');

            // Fill missing months with zero
            $data = [];
            foreach (range(1, $maxMonth) as $month) {
                $data[] = $monthlyTotals[$month] ?? 0;
            }

            return ['data' => $data, 'maxMonth' => $maxMonth];
        };

        // Collect data for 3 years
        $currentYearData = $getMonthlyTotals($year);
        $lastYearData = $getMonthlyTotals($lastYear);
        $last2YearData = $getMonthlyTotals($last2Year);

        // Labels for months
        $labels = array_slice(
            ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            0,
            max($currentYearData['maxMonth'], $lastYearData['maxMonth'], $last2YearData['maxMonth'])
        );

        return [
            'datasets' => [
                [
                    'label' => 'Imports in ' . $year,
                    'data' => $currentYearData['data'],
                    'borderColor' => '#f44336',
                    'backgroundColor' => '#f44336',
                    'pointBackgroundColor' => '#f44336',
                    'pointBorderColor' => '#f44336',
                    'borderWidth' => 2,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                    'tension' => 0.4, // curve between points
                ],
                [
                    'label' => 'Imports in ' . $lastYear,
                    'data' => $lastYearData['data'],
                    'borderColor' => '#adb5bd',
                    'backgroundColor' => '#adb5bd',
                    'pointBackgroundColor' => '#adb5bd',
                    'pointBorderColor' => '#adb5bd',
                    'borderWidth' => 2,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                    'tension' => 0.4,
                ],
                // To enable last 2-year data, uncomment below
                /*
                [
                    'label' => 'Imports in ' . $last2Year,
                    'data' => $last2YearData['data'],
                    'borderColor' => 'rgba(248, 127, 14, 0.99)',
                    'backgroundColor' => 'rgba(248, 127, 14, 0.99)',
                    'borderWidth' => 2,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                    'tension' => 0.4,
                ],
                */
            ],
            'labels' => $labels,
        ];
    }

    /**
     * Additional Chart.js configuration
     */
    protected function getOptions(): ?array
    {
        return [
            'plugins' => [
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
        ];
    }

    /**
     * Chart type: line
     */
    protected function getType(): string
    {
        return 'line';
    }
}
