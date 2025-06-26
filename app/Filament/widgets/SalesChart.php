<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;


class SalesChart extends ChartWidget
{
    protected static ?string $heading = 'Sale Chart';

    public ?string $filter = null;


    protected function getFilters(): ?array
    {
        $currentYear = now()->year;

        return collect(range($currentYear, $currentYear - 5))
            ->mapWithKeys(fn($year) => [$year => (string) $year])
            ->toArray();
    }

    protected function getData(): array
    {
        $year = $this->filter ?? now()->year;
        $lastyear = $year - 1;
        $last2year = $year - 2;
        $now = now();

        $getMonthlyTotals = function (int $targetYear) use ($now): array {
            $query = DB::table('sales')
                ->selectRaw('MONTH(sale_date) as month, SUM(total_pay) as total')
                ->whereYear('sale_date', $targetYear);

            if ($targetYear == $now->year) {
                $query->whereMonth('sale_date', '<=', $now->month);
            }

            $monthlyTotals = $query->groupBy('month')->pluck('total', 'month');

            $maxMonth = 12;
            $data = [];
            foreach (range(1, $maxMonth) as $month) {
                $data[] = $monthlyTotals[$month] ?? 0;
            }

            return ['data' => $data, 'maxMonth' => $maxMonth];
        };


        $currentYearData = $getMonthlyTotals($year);
        $lastYearData = $getMonthlyTotals($lastyear);
        $last2YearData = $getMonthlyTotals($last2year);

        $maxMonth = 12;
        $labels = array_slice(
            ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            0,
            $maxMonth
        );



        return [
            'datasets' => [
                [
                    'label' => 'Monthly Sales ' . $year,
                    'data' => $currentYearData['data'],
                    'borderColor' => 'rgba(28, 244, 49, 0.82)',
                    'backgroundColor' => 'rgba(28, 244, 49, 0.82)',
                    //'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Monthly Sales ' . $lastyear,
                    'data' => $lastYearData['data'],
                    'borderColor' => 'rgba(224, 225, 227, 0.97)',
                    'backgroundColor' => 'rgba(224, 225, 227, 0.97)',
                    //'fill' => true,
                    'tension' => 0.4,
                ],
                /*[
                    'label' => 'Monthly Sales ' . $last2year,
                    'data' => $last2YearData['data'],
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(21, 228, 42, 0.68)',
                    //'fill' => true,
                    'tension' => 0.4,
                ],*/
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
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
