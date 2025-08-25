<?php

namespace App\Filament\Resources\UserResource\Widgets;

use App\Filament\Resources\UserResource\Pages\ListUsers;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class UserWidget extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListUsers::class;
    }

    protected int|array $columns = 3;

    protected function getStats(): array
    {
        // Calculate total revenue from sales
        $saleTotal = DB::table('sales')->sum('total_pay');

        // Total quantity of items sold
        $itemsold = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->selectRaw('SUM(qty) as total')
            ->value('total') ?? 0;

        return [
            Stat::make('Total User', $this->getPageTableQuery('users')->count())
                ->chart([27, 27])
                ->color('info'),

            Stat::make('Total Revenue', '$' . number_format($saleTotal, 2))
                ->chart([27, 27])
                ->color('info'),

            Stat::make('Total Quantity Sold', $itemsold)
                ->chart([27, 27])
                ->color('info'),
        ];
    }
}
