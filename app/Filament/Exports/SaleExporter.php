<?php

namespace App\Filament\Exports;

use App\Models\Sale;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class SaleExporter extends Exporter
{
    protected static ?string $model = Sale::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('Invoice No')
                ->state(fn(Sale $record): string => 'INV-' . str_pad($record->id, 5, '0', STR_PAD_LEFT)),

            ExportColumn::make('customer.name')
                ->label('Customer Name'),

            ExportColumn::make('sale_date')
                ->label('Sale Date'),
            // ->state(fn(Sale $record): string => $record->sale_date->format('Y-m-d')),
            ExportColumn::make('products')
                ->label('Products')
                ->state(fn(Sale $record): string => $record->listProducts()),

            ExportColumn::make('total_qty')
                ->label('Total Quantity')
                ->state(fn(Sale $record): int => $record->total_qty),

            ExportColumn::make('total_amount')
                ->label('Total Amount')
                ->state(fn(Sale $record): string => number_format($record->total_price, 2)),

            ExportColumn::make('user.name')
                ->label('Cashier'),

            // ExportColumn::make('created_at')
            //     ->label('Created At')
            //     ->state(fn(Sale $record): string => $record->created_at->format('Y-m-d H:i')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your sale export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }

    public static function modifyQuery(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->with(['items', 'customer', 'user']);
    }
}
