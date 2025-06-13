<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Filament\Resources\SupplierResource\RelationManagers;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-m-arrow-path';

    protected static ?string $navigationGroup = 'Customer & Supplier';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('phone')
                    ->tel(),
                Forms\Components\TextInput::make('address'),
                Forms\Components\TextInput::make('bank_name'),
                Forms\Components\TextInput::make('account_number'),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('active')
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('bank_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('account_number')
                    ->searchable()
                    ->badge()
                    ->formatStateUsing(fn(string $state): string =>
                    implode(' ', str_split($state, 4))),

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
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Status')
                    ->placeholder('All Suppliers')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('activate')
                    ->label('Activate Selected')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->action(fn(Collection $records) => $records->each->update(['active' => true])),
                Tables\Actions\BulkAction::make('deactivate')
                    ->label('Deactivate Selected')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->action(fn(Collection $records) => $records->each->update(['active' => false])),
            ])
            ->defaultSort('active', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Supplier Information')
                    ->description('Basic supplier details')
                    ->icon('heroicon-m-building-office-2')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('name')
                                ->label('Supplier Name')
                                ->icon('heroicon-m-user')
                                ->weight(FontWeight::Medium),

                            TextEntry::make('phone')
                                ->label('Phone Number')
                                ->icon('heroicon-m-phone')
                                ->url(fn($record) => $record->phone ? 'tel:' . $record->phone : null)
                                ->color('primary'),

                            TextEntry::make('address')
                                ->label('Address')
                                ->icon('heroicon-m-map-pin')
                                ->columnSpanFull(),
                        ]),
                    ]),

                Section::make('Banking Information')
                    ->description('Financial and payment details')
                    ->icon('heroicon-m-banknotes')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('bank_name')
                                ->label('Bank Name')
                                ->icon('heroicon-m-building-library')
                                ->badge()
                                ->color('info'),

                            TextEntry::make('account_number')
                                ->label('Account Number')
                                ->icon('heroicon-m-credit-card')
                                ->badge()
                                ->color('gray')
                                ->formatStateUsing(
                                    fn(string $state): string =>
                                    implode(' ', str_split($state, 4))
                                ),
                        ]),
                    ]),

                Section::make('Status & Timestamps')
                    ->description('Supplier status and record information')
                    ->icon('heroicon-m-clock')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('active')
                                ->label('Status')
                                ->badge()
                                ->formatStateUsing(fn(bool $state): string => $state ? 'Active' : 'Inactive')
                                ->color(fn(bool $state): string => $state ? 'success' : 'danger')
                                ->icon(fn(bool $state): string => $state ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle'),

                            TextEntry::make('created_at')
                                ->label('Created')
                                ->icon('heroicon-m-calendar-days')
                                ->dateTime()
                                ->color('gray'),

                            TextEntry::make('updated_at')
                                ->label('Last Updated')
                                ->icon('heroicon-m-arrow-path')
                                ->dateTime()
                                ->color('gray'),
                        ]),
                    ]),
            ]);
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
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'supplier.view' => Pages\ViewSupplier::route(path: '/{record}'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
