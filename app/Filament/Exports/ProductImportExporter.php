<?php

namespace App\Filament\Exports;

use App\Models\ProductImport;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ProductImportExporter extends Exporter
{
    protected static ?string $model = ProductImport::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('products')
                ->label('Products')
                ->state(fn(ProductImport $record) => $record->listProducts()),
            ExportColumn::make('total_qyt')
                ->label('Total Qty')
                ->state(fn(ProductImport $record) => $record->totalQty()),
            ExportColumn::make('total_amount')
                ->label('Total Amount')
                ->state(fn(ProductImport $record) => $record->totalPrice()),
            ExportColumn::make('import_date')
                ->label('Import Date'),
            ExportColumn::make('supplier.name')
                ->label('Supplier'),
            ExportColumn::make('user.name')
                ->label('Importer')
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your product import export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
