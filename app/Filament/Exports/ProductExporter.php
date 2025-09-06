<?php

namespace App\Filament\Exports;

use App\Models\Product;
use Filament\Actions\Exports\Enums\Contracts\ExportFormat;
use Filament\Actions\Exports\Enums\ExportFormat as EnumsExportFormat;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use OpenSpout\Common\Entity\Style\Style;

class ProductExporter extends Exporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('name'),
            ExportColumn::make('brand.name'),
            ExportColumn::make('category.name'),
            ExportColumn::make('price'),
            ExportColumn::make('stock')
                ->label('In Stock'),
            // ExportColumn::make('stock_security'),
            ExportColumn::make('description'),
            ExportColumn::make('active'),

            // ExportColumn::make('user.name'),
            // ExportColumn::make('created_at'),
            // ExportColumn::make('updated_at'),
        ];
    }

    public function getXlsxCellStyle(): ?Style
    {
        return (new Style())
            ->setFontSize(12)
            ->setFontName('Arial')
            ->setCellAlignment(CellAlignment::CENTER)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER);;
    }

    // public function getFormats(): array
    // {
    //     return [
    //         EnumsExportFormat::Xlsx
    //     ];
    // }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your product export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
