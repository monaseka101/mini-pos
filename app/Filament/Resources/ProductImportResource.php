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
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
<<<<<<< HEAD
use Illuminate\Support\Facades\Log;
=======
use Illuminate\Support\Facades\Auth;
use App\Enums\Role;
use Filament\Facades\Filament;


use App\Exports\ProductImportItemsExport;
use App\Filament\Resources\ProductImportResource\Pages\EditProductImport;
use Filament\Forms\Components\DatePicker;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\Select;
>>>>>>> 8c30c670a9ec1afb31c671cb61f24a17e45bfe73

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
                            ->displayFormat('d/m/Y')
                            // ->native(false)
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
                                Select::make('product_id')
                                    ->label('Product')
                                    ->relationship('product', 'name', fn($query) => $query->where('active', true))
                                    ->preload()
                                    ->required()
                                    ->distinct()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
<<<<<<< HEAD
                                    ->searchable(),

                                Forms\Components\TextInput::make('qty')
=======
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(function (callable $set, $state) {
                                        $price = \App\Models\Product::find($state)?->price ?? 0;
                                        $set('product_price', $price);
                                    }),

                                TextInput::make('qty')
>>>>>>> 8c30c670a9ec1afb31c671cb61f24a17e45bfe73
                                    ->label('Quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->required(),

                                TextInput::make('unit_price')
                                    ->label('Unit Price')
                                    ->prefix('$')
                                    ->required(),

                                TextInput::make('product_price')
                                    ->label('Current Price')
                                    ->disabled()
                                    ->dehydrated(false) // prevents it from being saved to DB
                                    ->prefix('$')
                            ])
                            ->columns(4)
                            ->required()
                    ])
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
                                    Grid::make(4)
                                        ->schema([
                                            TextEntry::make('product.name')
                                                ->label('Product')
                                                ->weight(FontWeight::SemiBold)
                                                ->icon('heroicon-o-cube'),

                                            TextEntry::make('qty')
                                                ->label('Quantity')
                                                ->badge()
                                                ->color('info'),

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
<<<<<<< HEAD
                    ->label('ID')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('products')
                    ->label('Products')
                    ->getStateUsing(function (ProductImport $record) {
                        // Option 1: Simple approach (may cause N+1 queries)
                        return $record->listProducts();
                    }),

=======
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label("Id")
                    ->sortable(),
>>>>>>> 8c30c670a9ec1afb31c671cb61f24a17e45bfe73
                Tables\Columns\TextColumn::make('supplier.name')
                    ->url(fn($record) => SupplierResource::getUrl('supplier.view', ['record' => $record->supplier_id]), true)
                    ->tooltip("link to view supplier's information")
                    ->sortable(),
                Tables\Columns\TextColumn::make('import_date')
                    ->label('Import Date')
                    ->date('d/m/Y')
                    ->weight(FontWeight::Bold)
                    ->dateTooltip('d/M/Y')
                    ->sortable(),
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
                    ->money(currency: 'usd')
                    ->getStateUsing(fn(ProductImport $record) => $record->totalPrice())
<<<<<<< HEAD
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return ProductImport::sortByTotalPrice($query, $direction);
                    })
=======
>>>>>>> 8c30c670a9ec1afb31c671cb61f24a17e45bfe73
                    ->weight(FontWeight::Bold),
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
<<<<<<< HEAD
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

=======
                Tables\Filters\SelectFilter::make('import_date')
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
>>>>>>> 8c30c670a9ec1afb31c671cb61f24a17e45bfe73
                Tables\Filters\SelectFilter::make('supplier')
                    ->relationship('supplier', 'name')
                    ->preload()
                    ->searchable()
                    ->multiple(),
<<<<<<< HEAD
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
=======
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->preload()
                    ->searchable()
>>>>>>> 8c30c670a9ec1afb31c671cb61f24a17e45bfe73
                    ->multiple()
                    ->preload(),
            ])
            ->searchable()
            ->actions([
                Tables\Actions\ViewAction::make(),
<<<<<<< HEAD
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
                        ->formats([
                            ExportFormat::Xlsx,
                        ])
                ]
            )
            ->defaultSort('created_at', 'desc');
=======
                Tables\Actions\EditAction::make()
                    ->hidden(fn() => ! EditProductImport::canEdit()),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn() => ! EditProductImport::canEdit()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->hidden(fn() => ! EditProductImport::canEdit()),
                ]),
            ])
            ->HeaderActions([
                Tables\Actions\Action::make('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (): \Symfony\Component\HttpFoundation\BinaryFileResponse {
                        return Excel::download(new ProductImportItemsExport, 'productsImport.csv', \Maatwebsite\Excel\Excel::CSV);
                    }),

                Tables\Actions\Action::make('Export XLSX')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function (): \Symfony\Component\HttpFoundation\BinaryFileResponse {
                        return Excel::download(new ProductImportItemsExport, 'productsImport.xlsx');
                    }),
            ])
            ->recordUrl(function (ProductImport $record) {
                return Filament::auth()->user()->role === Role::Admin
                    ? Pages\EditProductImport::getUrl(['record' => $record])
                    : null;
            });
>>>>>>> 8c30c670a9ec1afb31c671cb61f24a17e45bfe73
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

    public static function getWidgets(): array
    {
        return [
            \App\Filament\Resources\ProductImportResource\Widgets\Importstats::class,
        ];
    }
    public static function canCreate(): bool
    {
        return Auth::user()?->role !== Role::Cashier;
    }
}
