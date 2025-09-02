<?php

namespace App\Filament\Pages;

use App\Filament\Resources\SupplierResource;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Models\ProductImport;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use App\Filament\Resources\ProductImportResource\Pages;


class ImportPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $model = ProductImport::class;

    protected static string $view = 'filament.pages.import-page';

    protected static ?string $title = 'Product Import Report';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected function getTableQuery()
    {
        return ProductImport::query()
            ->when(request('startDate') && request('endDate'), function ($query) {
                $query->whereBetween('import_date', [
                    request('startDate'),
                    request('endDate'),
                ]);
            })
        ;
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('id')
                ->toggleable(isToggledHiddenByDefault: true)
                ->label("Id")
                ->sortable(),

            Tables\Columns\TextColumn::make('supplier.name')
                ->searchable()
                ->sortable()
                // Link to the customer view page, opens in new tab
                ->url(fn($record) => SupplierResource::getUrl('supplier.view', ['record' => $record->supplier_id]), true),
            Tables\Columns\TextColumn::make('total_price')
                ->money(currency: 'usd')
                ->getStateUsing(fn(ProductImport $record) => $record->totalPrice())
                ->sortable()
                ->weight(FontWeight::Bold),
            Tables\Columns\TextColumn::make('note')
                ->toggleable(isToggledHiddenByDefault: true)
                ->html(),

            Tables\Columns\TextColumn::make('import_date')
                ->date('d/m/Y')
                ->dateTooltip('d/M/Y')
                ->sortable(),
            Tables\Columns\TextColumn::make('user.name')
                ->toggleable()
                ->label('Imported By'),

        ];
    }
    protected function getTableFilters(): array
    {
        return [
            Filter::make('import_date')
                ->form([
                    DatePicker::make('start')->label('Start Date'),
                    DatePicker::make('end')->label('End Date'),
                ])
                ->query(function ($query, array $data) {
                    if (!empty($data['start']) && !empty($data['end'])) {
                        $query->whereBetween('import_date', [$data['start'], $data['end']]);
                    } elseif (!empty($data['start'])) {
                        $query->where('import_date', '>=', $data['start']);
                    } elseif (!empty($data['end'])) {
                        $query->where('import_date', '<=', $data['end']);
                    }
                })
                ->default(fn() => [
                    'start' => request()->get('startDate'),
                    'end'   => request()->get('endDate'),
                ]),
            Tables\Filters\SelectFilter::make('supplier')
                ->preload()
                ->searchable()
                ->multiple()
                ->relationship('supplier', 'name'),

        ];
    }
    protected function getTableActions(): array
    {
        return [
            Tables\Actions\ViewAction::make(),
        ];
    }
    protected function getDefaultTableSortColumn(): ?string
    {
        return 'import_date';
    }

    protected function getDefaultTableSortDirection(): ?string
    {
        return 'desc';
    }
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductImports::route('/'),
        ];
    }
}
