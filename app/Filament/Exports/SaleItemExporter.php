<?php

namespace App\Filament\Exports;

use App\Models\SaleItem;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class SaleItemExporter extends Exporter
{
    protected static ?string $model = SaleItem::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            // ExportColumn::make('sale.sale_date')
            //     ->label('Sale Date'),
            ExportColumn::make('product.name'),
            ExportColumn::make('qty'),
            ExportColumn::make('unit_price'),
            ExportColumn::make('discount'),
            // ExportColumn::make('sub_total')
            //     ->state(fn(SaleItem $record) => $record->subTotal()),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your sale item export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
