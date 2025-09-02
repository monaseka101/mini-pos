<?php

namespace App\Filament\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Models\Sale;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;

class SalePage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $view = 'filament.pages.sale-page';

    protected static ?string $title = 'Sales Report';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected function getTableQuery()
    {
        return Sale::query()
            ->when(request('startDate') && request('endDate'), function ($query) {
                $query->whereBetween('sale_date', [
                    request('startDate'),
                    request('endDate'),
                ]);
            });
    }

    protected function getTableColumns(): array
    {
        return
            [
                Tables\Columns\TextColumn::make('id')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label("Id")
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable()
                    // Link to the customer view page, opens in new tab
                    ->url(
                        fn($record) => CustomerResource::getUrl('customer.view', ['record' => $record->customer_id]),
                        shouldOpenInNewTab: true
                    ),

                Tables\Columns\TextColumn::make('note')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->html(),

                Tables\Columns\TextColumn::make('total_price')
                    ->money(currency: 'usd')
                    ->getStateUsing(fn(Sale $record) => $record->totalPrice()) // Sum price before discount
                    ->sortable(false) // disable SQL sorting
                    ->badge()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('has_discount')
                    ->label('Discount?')
                    ->searchable()
                    ->getStateUsing(fn(Sale $record) => $record->getHasDiscountAttribute()),


                Tables\Columns\TextColumn::make('total_pay')
                    ->money(currency: 'usd')
                    ->getStateUsing(fn(Sale $record) => $record->totalPay()) // Sum price after discount
                    ->sortable()
                    ->badge()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('sale_date')
                    ->label('Sale Date')
                    ->sortable()
                    ->dateTime('d/m/Y')
                    ->dateTooltip('d/M/Y')
                    ->toggleable(),

            ];
    }

    protected function getTableFilters(): array
    {
        return [
            Filter::make('sale_date')
                ->form([
                    DatePicker::make('start')->label('Start Date'),
                    DatePicker::make('end')->label('End Date'),
                ])
                ->query(function ($query, array $data) {
                    if (!empty($data['start']) && !empty($data['end'])) {
                        $query->whereBetween('sale_date', [$data['start'], $data['end']]);
                    } elseif (!empty($data['start'])) {
                        $query->where('sale_date', '>=', $data['start']);
                    } elseif (!empty($data['end'])) {
                        $query->where('sale_date', '<=', $data['end']);
                    }
                })
                ->default(fn() => [
                    'start' => request()->get('startDate'),
                    'end'   => request()->get('endDate'),
                ]),
            Tables\Filters\SelectFilter::make('customer')
                ->preload()
                ->searchable()
                ->multiple()
                ->relationship('customer', 'name'),
            Tables\Filters\SelectFilter::make('has_discount')
                ->label('Discount?')
                ->options([
                    'yes' => 'Yes',
                    'no' => 'No',
                ])
                ->query(function ($query, $data) {
                    if (!isset($data['value'])) return;
                    if ($data['value'] === 'yes') {
                        $query->whereHas('saleItems', fn($q) => $q->where('discount_id', '>', 1));
                    } else {
                        $query->whereHas('saleItems', fn($q) => $q->where('discount_id', '=', 1));
                    }
                }),

        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            Tables\Actions\DeleteBulkAction::make(),
        ];
    }
    protected function getDefaultTableSortColumn(): ?string
    {
        return 'sale_date';
    }

    protected function getDefaultTableSortDirection(): ?string
    {
        return 'desc';
    }
}
