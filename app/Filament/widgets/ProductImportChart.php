<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;


class ProductImportChart extends ChartWidget
{
    protected static ?string $heading = 'Import Chart';

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
            $query = DB::table('product_import_items')
                ->join('product_imports', 'product_import_items.product_import_id', '=', 'product_imports.id')
                ->selectRaw('MONTH(product_imports.import_date) as month, SUM(product_import_items.qty * product_import_items.unit_price) as total')
                ->whereYear('product_imports.import_date', $targetYear);

            if ($targetYear == $now->year) {
                $query->whereMonth('product_imports.import_date', '<=', $now->month);
                $maxMonth = 12;
            } else {
                $maxMonth = 12;
            }
            $monthlyTotals = $query->groupBy('month')->pluck('total', 'month');

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
                    'label' => 'Imports in ' . $year,
                    'data' => $currentYearData['data'],
                    'borderColor' => 'rgba(244, 27, 27, 0.98)',
                    'backgroundColor' => 'rgba(244, 27, 27, 0.98)',
                    //'fill' => true,
                    'tension' => 0.4,

                ],
                [
                    'label' => 'Imports in ' . $lastyear,
                    'data' => $lastYearData['data'],
                    'borderColor' => 'rgba(202, 200, 200, 0.93)',
                    'backgroundColor' => 'rgb(220, 218, 218)',
                    //'fill' => true,
                    'tension' => 0.4,
                ],
                /*   // line for last 2 year
                [
                    'label' => 'Imports in ' . $last2year,
                    'data' => $last2YearData['data'],
                    'borderColor' => 'rgba(248, 127, 14, 0.99)',
                    'backgroundColor' => 'rgba(248, 127, 14, 0.99)',
                    //'fill' => true,
                    'tension' => 0.4,
                ],
                */

            ],
            'labels' => $labels,
        ];
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

    protected function getType(): string
    {
        return 'bar';
    }
}
