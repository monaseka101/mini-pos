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
        // Calculate total import cost 
        $importcost = DB::table('product_import_items')
            ->join('product_imports', 'product_import_items.product_import_id', '=', 'product_imports.id')
            ->selectRaw('SUM(product_import_items.qty * product_import_items.unit_price) as total')
            ->value('total') ?? 0;

        // Calculate total number of items imported
        $importitem = DB::table('product_import_items')
            ->join('product_imports', 'product_import_items.product_import_id', '=', 'product_imports.id')
            ->selectRaw('SUM(product_import_items.qty ) as total')
            ->value('total') ?? 0;

        return [
            Stat::make('Total Imports', $this->getPageTableQuery()->count())->chart([27, 27])
                ->color('info'),

            Stat::make('Total Import Cost', '$' . number_format($importcost, 2))
                ->chart([27, 27])
                ->color('info'),

            Stat::make('Total import items', $importitem)
                ->chart([27, 27])
                ->color('info'),
        ];
    }
}
