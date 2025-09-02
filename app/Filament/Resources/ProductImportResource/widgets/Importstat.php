<?php

namespace App\Filament\Resources\ProductImportResource\Widgets;

use App\Filament\Resources\ProductImportResource\Pages\ListProductImports;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class Importstats extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListProductImports::class;
    }

    protected function getStats(): array
    {
        // Base filtered query from table page
        $baseQuery = $this->getPageTableQuery()->getQuery();

        // Clone query for aggregations to avoid messing up table query
        $costQuery = clone $baseQuery;
        $itemQuery = clone $baseQuery;

        // Total import cost
        $importcost = $costQuery
            ->join('product_import_items', 'product_import_items.product_import_id', '=', 'product_imports.id')
            ->sum(DB::raw('product_import_items.qty * product_import_items.unit_price'));

        // Total items imported
        $importitem = $itemQuery
            ->join('product_import_items', 'product_import_items.product_import_id', '=', 'product_imports.id')
            ->sum('product_import_items.qty');

        // Import record count
        $importCount = $baseQuery->count();

        return [
            Stat::make('Import record', $importCount)->chart([27, 27])->color('info'),
            Stat::make('Total Import Cost', '$' . number_format($importcost, 2))->chart([27, 27])->color('info'),
            Stat::make('Total items imported', $importitem)->chart([27, 27])->color('info'),
        ];
    }
}
