<?php

namespace App\Filament\Resources;

use App\Filament\Exports\ProductImportExporter;
use App\Filament\Resources\ProductImportResource\Pages;
use App\Filament\Resources\ProductImportResource\RelationManagers;
use App\Models\Product;
use App\Models\ProductImport;
use Filament\Actions\Action;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;

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
                Section::make('Product Items')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Product')
                                    ->relationship('product', 'name', fn($query) => $query->where('active', true)->orderBy('stock'))
                                    ->preload()
                                    ->required()
                                    ->distinct()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->searchable(),

                                Forms\Components\TextInput::make('qty')
                                    ->label('Quantity')
                                    ->numeric()
                                    ->extraAttributes([
                                        'onkeydown' => "if(['e','E','+','-'].includes(event.key)) event.preventDefault();",
                                    ])
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
                    ]),
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
                            ->displayFormat('d/m/Y')
                            // ->native(false)
                            ->default(now())
                            ->hidden()
                            ->required(),
                        Forms\Components\RichEditor::make('note')
                            ->columnSpan('full'),
                    ])->columns(2),

            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\Section::make('Product Import Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('id')
                                    ->label('Import ID')
                                    ->badge()
                                    ->color('primary'),

                                TextEntry::make('import_date')
                                    ->label('Import Date')
                                    ->date('d/m/Y')
                                    ->icon('heroicon-o-calendar-days'),

                                TextEntry::make('created_at')
                                    ->label('Created')
                                    ->since()
                                    ->icon('heroicon-o-clock'),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextEntry::make('supplier.name')
                                    ->label('Supplier')
                                    ->icon('heroicon-o-building-office-2')
                                    ->weight(FontWeight::SemiBold),

                                TextEntry::make('user.name')
                                    ->label('Created by')
                                    ->icon('heroicon-o-user-circle')
                                    ->badge()
                                    ->color('success'),
                            ]),
                        Grid::make(1)
                            ->schema([
                                TextEntry::make('note')
                                    ->label('Notes')
                                    ->html()
                                    ->extraAttributes([
                                        'class' => 'p-4 bg-gray-50 rounded-lg',
                                    ])
                            ])
                    ])

                    ->columns(1),

                \Filament\Infolists\Components\Section::make('Product Items')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->schema([
                                Split::make([
                                    Grid::make(6)
                                        ->schema([
                                            TextEntry::make('product.name')
                                                ->label('Product')
                                                ->weight(FontWeight::SemiBold)
                                                ->icon('heroicon-o-cube'),

                                            TextEntry::make('qty')
                                                ->label('Quantity')
                                                ->badge()
                                                ->color('info'),
                                            TextEntry::make('product.brand.name')
                                                ->label('Brand')
                                                ->badge()
                                                ->color('success'),
                                            TextEntry::make('product.category.name')
                                                ->label('Category')
                                                ->badge()
                                                ->color('success'),

                                            TextEntry::make('unit_price')
                                                ->label('Unit Price')
                                                ->money('USD')
                                                ->icon('heroicon-o-currency-dollar'),

                                            TextEntry::make('sub_total')
                                                ->label('Sub Total')
                                                ->money('USD')
                                                ->weight(FontWeight::Bold)
                                                ->color('success')
                                                ->state(function ($record) {
                                                    return $record->qty * $record->unit_price;
                                                }),
                                        ]),
                                ])
                            ])
                            ->contained(false)
                            ->hiddenLabel(),

                        Grid::make(4)
                            ->schema([
                                // TextEntry::make('total_items')
                                //     ->label('Total Items')
                                //     ->state(function ($record) {
                                //         return $record->items->sum('qty');
                                //     })
                                //     ->badge()
                                //     ->color('info')
                                //     ->icon('heroicon-o-list-bullet'),
                                TextEntry::make('d')
                                    ->label(''),
                                TextEntry::make('s')
                                    ->label(''),
                                TextEntry::make('x')
                                    ->label(''),

                                TextEntry::make('total_amount')
                                    ->label('Total Amount')
                                    ->state(function ($record) {
                                        return $record->items->sum(function ($item) {
                                            return $item->qty * $item->unit_price;
                                        });
                                    })
                                    ->money('USD')
                                    ->size('lg')
                                    ->weight(FontWeight::Bold)
                                    ->color('success')
                                    ->icon('heroicon-o-currency-dollar'),
                            ])
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('products')
                    ->label('Products')
                    ->wrap()
                    ->getStateUsing(function (ProductImport $record) {
                        // Option 1: Simple approach (may cause N+1 queries)
                        return $record->listProducts();
                    }),
                Tables\Columns\TextColumn::make('total_qty')
                    ->label('Total Qty')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        $result =  $query
                            ->join('product_import_items', 'product_imports.id', '=', 'product_import_items.product_import_id')
                            ->groupBy('product_imports.id')
                            ->selectRaw('product_imports.*, SUM(product_import_items.qty) as total_qty')
                            ->orderBy('total_qty', $direction);
                        Log::info($result->get());
                        return $result;
                    })
                    ->weight(FontWeight::Bold)
                    ->getStateUsing(function (ProductImport $record) {
                        // Option 1: Simple approach (may cause N+1 queries)
                        return $record->totalQty();
                    }),

                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total Price')
                    ->color('danger')
                    ->money(currency: 'usd')
                    ->getStateUsing(fn(ProductImport $record) => $record->totalPrice())
                    ->sortable(query: fn(Builder $query, string $direction) => ProductImport::sortByTotalPrice($query, $direction))
                    ->weight(FontWeight::Bold),

                Tables\Columns\TextColumn::make('import_date')
                    ->label('Import Date')
                    ->date('d/m/Y')
                    ->dateTooltip('d/M/Y')
                    ->sortable(),


                Tables\Columns\TextColumn::make('supplier.name')
                    ->url(fn($record) => SupplierResource::getUrl('supplier.view', ['record' => $record->supplier_id]), true)
                    ->tooltip("link to view supplier's information")
                    ->sortable(),
                Tables\Columns\TextColumn::make('note')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->html(),

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
                Filter::make('import_date')
                    ->form([
                        DatePicker::make('import_from')
                            ->label('Import From'),
                        DatePicker::make('import_util')
                            ->label('Import Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['import_from'],
                                callback: fn(Builder $query, $date): Builder => $query->whereDate('import_date', '>=', $date),
                            )
                            ->when(
                                $data['import_util'],
                                fn(Builder $query, $date): Builder => $query->whereDate('import_date', '<=', $date),
                            );
                    }),
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from_date'),
                        Forms\Components\DatePicker::make('to_date'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from_date'], fn($q) => $q->whereDate('import_date', '>=', $data['from_date']))
                            ->when($data['to_date'], fn($q) => $q->whereDate('import_date', '<=', $data['to_date']));
                    }),

                Tables\Filters\SelectFilter::make('supplier')
                    ->relationship('supplier', 'name')
                    ->preload()
                    ->searchable()
                    ->multiple(),
                Tables\Filters\SelectFilter::make('importer')
                    ->relationship('user', 'name')
                    ->preload()
                    ->searchable()
                    ->multiple(),
                Tables\Filters\SelectFilter::make('product')
                    ->label('Product')
                    ->options(function () {
                        return Product::query()
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            filled($data['values']),
                            fn(Builder $query): Builder => $query->whereHas(
                                'items.product',
                                fn(Builder $query): Builder => $query->whereIn('id', $data['values'])
                            )
                        );
                    })
                    // ->searchable()
                    ->multiple()
                    ->preload(),
            ])
            ->searchable()
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (ProductImport $record) {
                        foreach ($record->items as $item) {
                            $product = $item->product;
                            $product->decrement('stock', $item->qty);
                        }
                    })
            ])
            ->bulkActions(
                actions: [
                    ExportBulkAction::make()
                        ->color('primary')
                        ->exporter(ProductImportExporter::class)
                    /* ->formats([
                            ExportFormat::Xlsx,
                        ]) */
                ]
            )
            ->defaultSort('created_at', 'desc');
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
            // 'edit' => Pages\EditProductImport::route('/{record}/edit'),
        ];
    }
}
