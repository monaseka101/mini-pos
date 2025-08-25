<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SalesChart extends ChartWidget
{
    // Widget heading title
    protected static ?string $heading = 'Monthly Sale Chart';

    // Stores selected year filter
    public ?string $filter = null;

    /**
     * Filter dropdown: list of years (this year to 5 years ago)
     */
    protected function getFilters(): ?array
    {
        $currentYear = now()->year;

        return collect(range($currentYear, $currentYear - 5))
            ->mapWithKeys(fn($year) => [$year => (string) $year])
            ->toArray();
    }

    /**
     * Generates monthly sales chart data for the current and past years
     */
    protected function getData(): array
    {
        $year = $this->filter ?? now()->year;
        $lastYear = $year - 1;
        $last2Year = $year - 2;
        $now = now();

        /**
         * Fetch monthly total_pay for a given year.
         * Ensures missing months are filled with 0.
         */
        $getMonthlyTotals = function (int $targetYear) use ($now): array {
            $query = DB::table('sales')
                ->selectRaw('MONTH(sale_date) as month, SUM(total_pay) as total')
                ->whereYear('sale_date', $targetYear);

            // Only fetch up to the current month if it's this year
            if ($targetYear === $now->year) {
                $query->whereMonth('sale_date', '<=', $now->month);
            }

            // Get result as [month => total]
            $monthlyTotals = $query->groupBy('month')->pluck('total', 'month');

            // Fill all months from 1 to 12
            $data = [];
            foreach (range(1, 12) as $month) {
                $data[] = $monthlyTotals[$month] ?? 0;
            }

            return ['data' => $data];
        };

        // Prepare data for current and past years
        $currentYearData = $getMonthlyTotals($year);
        $lastYearData = $getMonthlyTotals($lastYear);
        $last2YearData = $getMonthlyTotals($last2Year);

        // Month labels
        $labels = [
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'May',
            'Jun',
            'Jul',
            'Aug',
            'Sep',
            'Oct',
            'Nov',
            'Dec'
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Monthly Sales ' . $year,
                    'data' => $currentYearData['data'],
                    'borderColor' => '#22c55e',
                    'backgroundColor' => '#22c55e',
                    'pointBorderColor' => '#22c55e',
                    'pointBackgroundColor' => '#22c55e',
                    'borderWidth' => 2,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                    'tension' => 0.4, // smooth curve
                ],
                [
                    'label' => 'Monthly Sales ' . $lastYear,
                    'data' => $lastYearData['data'],
                    'borderColor' => '#adb5bd',
                    'backgroundColor' => '#adb5bd',
                    'pointBorderColor' => '#adb5bd',
                    'pointBackgroundColor' => '#adb5bd',
                    'borderWidth' => 2,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                    'tension' => 0.4,
                ],
                // Optional: enable 2 years ago comparison
                /*
                [
                    'label' => 'Monthly Sales ' . $last2Year,
                    'data' => $last2YearData['data'],
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 12, 231, 0.68)',
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

    protected function getType(): string
    {
        return 'line';
    }
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
}
