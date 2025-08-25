<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\SaleResource;
use App\Filament\Resources\CustomerResource;
use App\Models\Sale;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;


class LatestSale extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 3;

    protected static ?string $heading = 'Recent Sale';

    public function table(Table $table): Table
    {
        return $table
            ->query(SaleResource::getEloquentQuery())
            ->defaultPaginationPageOption(5)
            ->defaultSort('sale_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('sale_date')
                    ->label('Order Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('id')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->label("Sale Id")
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable()
                    ->url(
                        fn($record) => CustomerResource::getUrl('customer.view', ['record' => $record->customer_id]),
                        shouldOpenInNewTab: true
                    ),
                /* Tables\Columns\TextColumn::make('finished')
                    ->label('Status')
                    ->state('Finished')
                    ->formatStateUsing(fn() => 'Finished')
                    ->sortable()
                    ->badge()
                    ->color('success'), */
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Sold By')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_pay')
                    ->money(currency: 'usd')
                    ->getStateUsing(fn(Sale $record) => $record->totalPay())
                    ->sortable()
                    ->badge()
                    ->toggleable(),

            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(
                        fn(Sale $record) => SaleResource::getUrl('view2', ['record' => $record])
                    )
                // ->openUrlInNewTab(),
            ]);
    }
}
