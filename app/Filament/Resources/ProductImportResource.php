<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductImportResource\Pages;
use App\Filament\Resources\ProductImportResource\RelationManagers;
use App\Models\Product;
use App\Models\ProductImport;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductImportResource extends Resource
{
    protected static ?string $model = ProductImport::class;

    protected static ?string $navigationIcon = 'heroicon-m-arrow-down-tray';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Forms\Components\Select::make('supplier_id')
                            ->label('Supplier')
                            ->relationship('supplier', 'name', modifyQueryUsing: fn(Builder $query) => $query->where('active', true))
                            ->preload()
                            ->searchable()
                            ->required(),
                        Forms\Components\DatePicker::make('import_date')
                            ->label('Import Date')
                            ->default(now())
                            ->required(),
                        Forms\Components\RichEditor::make('note')
                            ->columnSpan('full'),
                    ])->columns(2),
                Section::make('Product Items')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Product')
                                    ->relationship('product', 'name', fn($query) => $query->where('active', true))
                                    ->preload()
                                    ->required()
                                    ->distinct()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()

                                    ->searchable(),

                                Forms\Components\TextInput::make('qty')
                                    ->label('Quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->required(),

                                Forms\Components\TextInput::make('unit_price')
                                    ->label('Unit Price')
                                    ->prefix('$')
                                    ->required(),
                            ])
                            ->orderColumn('')
                            ->defaultItems(1)
                            ->hiddenLabel()
                            ->columns(3)
                            ->required()
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('supplier.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->money(currency: 'usd')
                    ->getStateUsing(fn(ProductImport $record) => $record->totalPrice())
                    ->sortable()
                    ->weight(FontWeight::Bold),

                Tables\Columns\TextColumn::make('import_date')
                    ->date('d/m/Y')
                    ->dateTooltip('d/M/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->toggleable()
                    ->label('Imported By'),
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
                Tables\Filters\SelectFilter::make('supplier')
                    ->relationship('supplier', 'name')
                    ->preload()
                    ->searchable()
                    ->multiple()
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListProductImports::route('/'),
            'create' => Pages\CreateProductImport::route('/create'),
            'edit' => Pages\EditProductImport::route('/{record}/edit'),
        ];
    }
}
