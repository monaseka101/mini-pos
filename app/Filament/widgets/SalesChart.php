<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class SalesChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Sales Chart';

    protected function getData(): array
    {
        // Access dashboard filters
        $startDate = $this->filters['startDate'] ?? now()->startOfMonth();
        $endDate = $this->filters['endDate'] ?? now();

        $start = \Carbon\Carbon::parse($startDate)->startOfMonth();
        $end = \Carbon\Carbon::parse($endDate)->endOfMonth();

        // Fetch monthly totals
        $sales = DB::table('sales')
            ->selectRaw('DATE_FORMAT(sale_date, "%Y-%m") as month, SUM(total_pay) as total')
            ->whereBetween('sale_date', [$start, $end])
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $months = array_keys($sales);
        $totals = array_values($sales);
        $count = count($totals);

        // Downsample if more than 30 points, but last point always end month
        if ($count > 40) {
            $step = ($count - 1) / 39; // 29 points + last point
            $newTotals = [];
            $newMonths = [];
            for ($i = 0; $i < 39; $i++) {
                $index = (int) floor($i * $step);
                $newTotals[] = $totals[$index];
                $newMonths[] = $months[$index];
            }
            // Last point is end month
            $newTotals[] = $totals[$count - 1];
            $newMonths[] = $months[$count - 1];

            $totals = $newTotals;
            $months = $newMonths;
        }

        return [
            'labels' => $months,
            'datasets' => [
                [
                    'label' => 'Sales',
                    'data' => $totals,
                    'fill' => true,
                    'borderColor' => '#22c55e',
                    'backgroundColor' => '#22c55e33',
                    'pointBackgroundColor' => 'rgba(50, 220, 38, 0.82)',
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
