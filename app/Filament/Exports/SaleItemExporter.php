<?php

namespace App\Filament\Exports;

use App\Models\SaleItem;
use Carbon\Carbon;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Forms\Components\Builder;

class SaleItemExporter extends Exporter
{
    protected static ?string $model = SaleItem::class;



    public static function getColumns(): array
    {
        return [
            ExportColumn::make('sale.id')
                ->label('Sale ID'),

            ExportColumn::make('sale.sale_date')
                ->label('Sale Date')
                ->formatStateUsing(
                    fn($state) =>
                    $state ? Carbon::parse($state)->format('d/m/Y') : null
                ),

            ExportColumn::make('sale.customer.name')
                ->label('Customer'),

            ExportColumn::make('sale.user.name')
                ->label('Sold By'),

            ExportColumn::make('product.name')
                ->label('Product'),

            ExportColumn::make('qty')
                ->label('Quantity'),

            ExportColumn::make('unit_price')
                ->label('Unit Price'),

            ExportColumn::make('subtotal')
                ->label('Subtotal')
                ->state(
                    fn(SaleItem $item) =>
                    $item->qty * $item->unit_price
                ),

            ExportColumn::make('total_sale_amount')
                ->label('Total Sale Amount')
                ->state(
                    fn(SaleItem $item) =>
                    optional($item->sale?->items)
                        ?->sum(fn($i) => $i->qty * $i->unit_price) ?? 0
                ),
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
