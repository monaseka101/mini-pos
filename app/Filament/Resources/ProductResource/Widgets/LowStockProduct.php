<?php

namespace App\Filament\Resources\ProductResource\Widgets;

use App\Filament\Resources\ProductImportResource;
use App\Filament\Resources\ProductResource;
use App\Helpers\Util;
use Filament\Tables\Actions;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Log;

class LowStockProduct extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Low Stock Alert.';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProductResource::getEloquentQuery()->whereColumn('stock', '<=', 'stock_security')->where('active', true)
            )
            ->defaultSort('stock', 'asc')
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\ImageColumn::make('image')
                    ->size(60)
                    ->defaultImageUrl(fn($record) => Util::getDefaultAvatar($record->name)),
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->weight(FontWeight::Bold)
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->badge()
                    ->color(
                        fn($record) =>
                        $record->stock <= 0 ? 'danger' : ($record->stock <= $record->stock_security ? 'warning' : 'success')
                    )
                    ->icon(
                        fn($record) =>
                        $record->stock <= 0 ? 'heroicon-m-x-circle' : ($record->stock <= $record->stock_security ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                    )
                    ->tooltip(
                        fn($record) =>
                        $record->stock <= 0 ? 'Out of stock' : ($record->stock <= $record->stock_security ? 'Low stock - below security level' : 'Stock level is good')
                    ),

                Tables\Columns\TextColumn::make('brand.name')
                    ->label('Brand')
                    ->badge()
                    ->color(color: 'primary'),
                Tables\Columns\TextColumn::make('category.name')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('sale_items_sum_qty')
                    ->label('Sold Count')
                    ->weight(FontWeight::Bold)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('description')
                    ->toggleable(true)
                    ->limit(120)
                    ->wrap()
                    ->html(),
                Tables\Columns\TextColumn::make('user.name')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Created By'),
                Tables\Columns\IconColumn::make('active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->preload()
                    ->multiple(),
                Tables\Filters\SelectFilter::make('brand')
                    ->relationship('brand', 'name')
                    ->preload()
                    ->multiple(),
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Status')
                    ->placeholder('All Products')
                    ->trueLabel('Active Products')
                    ->falseLabel('Inactive Products')
            ])
            ->headerActions([
                Actions\Action::make('to_import')
                    ->url('admin/import-page')
                    ->label('Import Products')
                    ->icon('heroicon-o-plus')
                // ->color('success')
            ]);
    }
}
