<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Filament\Resources\DiscountResource\Pages;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Discount;
use Filament\Resources\Resource;

class DiscountResource extends Resource
{
    protected static ?string $model = Discount::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Discounts';
    protected static ?string $pluralModelLabel = 'Discounts';
    protected static ?string $navigationGroup = 'Inventory';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('value')
                ->label('Discount Value')
                ->required()
                ->numeric(),

            Forms\Components\Toggle::make('ispercent')
                ->label('Is Percent?'),

            Forms\Components\Toggle::make('active')
                ->label('Active'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->sortable()->searchable(),

            Tables\Columns\TextColumn::make('value')
                ->label('Value'),

            Tables\Columns\IconColumn::make('ispercent')
                ->label('%?')
                ->boolean(),

            Tables\Columns\IconColumn::make('active')
                ->label('Active')
                ->boolean(),

            Tables\Columns\TextColumn::make('created_at')->dateTime()->since(),
        ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDiscounts::route('/'),
            'create' => Pages\CreateDiscount::route('/create'),
            'edit' => Pages\EditDiscount::route('/{record}/edit'),
        ];
    }
}
