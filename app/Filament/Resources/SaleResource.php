<?php

namespace App\Filament\Resources;

use App\Filament\Exports\DetailedSaleExporter;
use App\Filament\Exports\SaleExporter;
use App\Filament\Exports\SaleItemExporter;
use App\Filament\Resources\SaleResource\Widgets\SaleStats;
use App\Models\Sale;
use App\Models\Customer;
use Filament\Actions\ExportAction;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use App\Filament\Resources\SaleResource\Pages;
use App\Helpers\Util;
use Filament\Actions\Action;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Forms\Components\DatePicker;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Repeater;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Split;
use Filament\Support\Enums\FontWeight;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Infolist;
<<<<<<< HEAD
use Filament\Tables\Actions\ExportAction as ActionsExportAction;
use Filament\Tables\Actions\ExportBulkAction;
use Illuminate\Support\Facades\Log;
use OpenSpout\Common\Entity\Style\CellAlignment;
=======
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesItemExport;
use App\Filament\Exports\SaleItemExporter;
use App\Filament\Resources\SaleResource\Widgets\Salestats;
use Filament\Forms\Components\Slider;
use Filament\Tables\Filters\Filter;

>>>>>>> 8c30c670a9ec1afb31c671cb61f24a17e45bfe73

class SaleResource extends Resource
{
    // Bind the Eloquent model
    protected static ?string $model = Sale::class;

    // Icons and labels for navigation in Filament admin panel
    protected static ?string $navigationIcon = 'heroicon-m-shopping-cart';
    protected static ?string $navigationLabel = 'Sales';
    protected static ?string $modelLabel = 'Sale';
    protected static ?string $pluralModelLabel = 'Sales';

    /**
     * Define the form schema for creating and editing a Sale
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Group sections to control layout
                Forms\Components\Group::make()
                    ->schema([
                        // Sale details section (customer, sale date, note)
                        Forms\Components\Section::make()
                            ->schema(static::getDetailsFormSchema())
                            ->columns(2),

                        // Section to add sale items with a repeater component
                        Forms\Components\Section::make('Sale Items')
                            ->schema([
                                static::getItemsRepeater(),
                            ]),
                    ])
                    // Responsive column span depending on if record exists
                    ->columnSpan(['lg' => fn(?Sale $record) => $record === null ? 3 : 2]),
            ]);
    }

<<<<<<< HEAD
=======
    /**
     * Returns form components for the Sale details section
     */
>>>>>>> 8c30c670a9ec1afb31c671cb61f24a17e45bfe73
    public static function getDetailsFormSchema(): array
    {
        return [
            Forms\Components\Select::make('customer_id')
                ->relationship(
                    'customer',
                    'name',
                    fn($query) => $query->where('active', true) // only active customers
                )
                ->preload()
                ->searchable()
                ->required()
                ->default(1)
                ->createOptionForm([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('phone')
                        ->maxLength(255),
                ]),

            // Sale date picker with default as now
            DatePicker::make('sale_date')
                ->default(now()),

            // Optional rich text note
            Forms\Components\RichEditor::make('note')
                ->columnSpan('full'),
        ];
    }

    /**
     * Defines the repeater used to input multiple sale items
     */
    public static function getItemsRepeater(): Repeater
    {
        return Repeater::make('items')
            ->relationship() // Binds to related sale items
            ->schema([
                // Product select dropdown (only active products)
                Forms\Components\Select::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'name', fn(Builder $query) => $query->where('active', true)->where('stock', '>', 0))
                    ->preload()
                    ->required()
                    ->afterStateUpdated(function ($state, Set $set) {
                        // Automatically set unit price and available stock when product selected
                        $product = Product::find($state);
                        if ($product) {
                            $set('unit_price', $product->price ?? 0);
                            $set('available_stock', $product->stock ?? 0);
                        }
                    })
                    ->distinct()
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                    ->searchable(),

                // Quantity input, validated against stock availability
                Forms\Components\TextInput::make('qty')
                    ->label('Quantity')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->maxValue(fn(Get $get) => $get('available_stock'))
                    ->validationMessages(['max' => 'The product in stock have only :max.'])
                    ->required(),

<<<<<<< HEAD
                Forms\Components\TextInput::make('discount')
                    ->default(0),
=======
                // Stock display (disabled, not submitted)
>>>>>>> 8c30c670a9ec1afb31c671cb61f24a17e45bfe73
                Forms\Components\TextInput::make('available_stock')
                    ->label('In Stock')
                    ->disabled()
                    ->dehydrated(false),

                // Unit price input, disabled but required (comes from product)
                Forms\Components\TextInput::make('unit_price')
                    ->label('Unit Price')
                    ->disabled()
                    ->dehydrated()
                    ->prefix('$')
                    ->required(),

                // Discount selection for this item, reactive to set discount values
                Forms\Components\Select::make('discount_id')
                    ->label('Discount')
                    ->relationship('discount', 'name', fn($query) => $query->where('active', true))
                    ->searchable()
                    ->preload()
                    ->afterStateUpdated(function ($state, Set $set) {
                        $discount = \App\Models\Discount::find($state);
                        if ($discount) {
                            $set('discount_value', $discount->value);
                            $set('is_percent', $discount->ispercent);
                        } else {
                            $set('discount_value', 0);
                            $set('is_percent', 0);
                        }
                    })
                    ->default(1)
                    ->required(),
            ])
            ->orderColumn('') // No ordering column specified
            ->defaultItems(1) // At least one item by default
            ->hiddenLabel()
            ->columns(5)
            ->required();
    }

<<<<<<< HEAD
    public static function getWidgets(): array
    {
        return [
            SaleStats::class
        ];
    }

    // public static function table(Table $table): Table
    // {
    //     return $table
    //         ->columns([
    //             Tables\Columns\TextColumn::make('id')
    //                 ->label('Sale #')
    //                 ->sortable(),

    //             Tables\Columns\TextColumn::make('customer.name')
    //                 ->label('Customer')
    //                 ->searchable()
    //                 ->sortable(),

    //             Tables\Columns\TextColumn::make('sale_date')
    //                 ->label('Sale Date')
    //                 ->date()
    //                 ->sortable(),

    //             Tables\Columns\TextColumn::make('saleItems')
    //                 ->label('Items')
    //                 ->badge()
    //                 ->getStateUsing(fn($record) => $record->saleItems->count())
    //                 ->color('gray'),

    //             Tables\Columns\TextColumn::make('total')
    //                 ->label('Total')
    //                 ->money('USD')
    //                 ->getStateUsing(function ($record) {
    //                     return $record->saleItems->sum(function ($item) {
    //                         $subtotal = $item->qty * $item->unit_price;
    //                         $discount = $subtotal * (($item->discount ?? 0) / 100);
    //                         return $subtotal - $discount;
    //                     });
    //                 })
    //                 ->sortable(query: function (Builder $query, string $direction): Builder {
    //                     return $query->withSum(
    //                         'saleItems as total',
    //                         'qty * unit_price * (1 - COALESCE(discount, 0) / 100)'
    //                     )->orderBy('total', $direction);
    //                 }),

    //             Tables\Columns\IconColumn::make('active')
    //                 ->boolean()
    //                 ->sortable(),

    //             Tables\Columns\TextColumn::make('created_at')
    //                 ->label('Created')
    //                 ->dateTime()
    //                 ->sortable()
    //                 ->toggleable(isToggledHiddenByDefault: true),
    //         ])
    //         ->filters([
    //             Tables\Filters\TernaryFilter::make('active')
    //                 ->label('Status')
    //                 ->boolean()
    //                 ->trueLabel('Active sales only')
    //                 ->falseLabel('Inactive sales only')
    //                 ->native(false),

    //             Tables\Filters\Filter::make('sale_date')
    //                 ->form([
    //                     DatePicker::make('from')
    //                         ->label('From Date'),
    //                     DatePicker::make('until')
    //                         ->label('Until Date'),
    //                 ])
    //                 ->query(function (Builder $query, array $data): Builder {
    //                     return $query
    //                         ->when(
    //                             $data['from'],
    //                             fn(Builder $query, $date): Builder => $query->whereDate('sale_date', '>=', $date),
    //                         )
    //                         ->when(
    //                             $data['until'],
    //                             fn(Builder $query, $date): Builder => $query->whereDate('sale_date', '<=', $date),
    //                         );
    //                 }),
    //         ])
    //         ->actions([
    //             Tables\Actions\EditAction::make(),
    //             Tables\Actions\DeleteAction::make(),
    //         ])
    //         ->bulkActions([
    //             Tables\Actions\BulkActionGroup::make([
    //                 Tables\Actions\DeleteBulkAction::make(),
    //             ]),
    //         ])
    //         ->defaultSort('created_at', 'desc');
    // }

=======
    /**
     * Defines the table listing sales with sorting, filtering, and actions
     */
>>>>>>> 8c30c670a9ec1afb31c671cb61f24a17e45bfe73
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Sale #')
                    ->formatStateUsing(fn($state) => Util::formatSaleId($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->tooltip('View customer information')
                    ->searchable()
                    ->sortable()
                    // Link to the customer view page, opens in new tab
                    ->url(
                        fn($record) => CustomerResource::getUrl('customer.view', ['record' => $record->customer_id]),
                        shouldOpenInNewTab: true
                    ),

<<<<<<< HEAD
                Tables\Columns\TextColumn::make('products')
                    ->label('Products')
                    ->getStateUsing(fn(Sale $record) => $record->listProducts()),

                Tables\Columns\TextColumn::make('total_qty')
                    ->label('Total Qty')
                    ->weight(FontWeight::Bold)
                    ->getStateUsing(fn(Sale $record) => $record->total_qty),

                Tables\Columns\TextColumn::make('total_price')
                    ->money(currency: 'usd')
                    ->getStateUsing(fn(Sale $record) => $record->total_price)
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
                            ->groupBy('sales.id')
                            ->selectRaw('sales.*, SUM((sale_items.unit_price * sale_items.qty) * (1 - COALESCE(sale_items.discount, 0)/100)) as total_price')
                            ->orderBy('total_price', $direction);
                    })

                    ->badge()
                    ->color('success'),
                // ->toggleable(),

=======
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
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Seller')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
>>>>>>> 8c30c670a9ec1afb31c671cb61f24a17e45bfe73

                Tables\Columns\TextColumn::make('sale_date')
                    ->label('Sale Date')
                    ->sortable()
                    ->dateTime('d/m/Y')
                    ->dateTooltip('d/M/Y')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->label('Sold By'),
                Tables\Columns\TextColumn::make('note')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->html(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->sortable()
                    ->dateTime('d/m/Y')
                    ->dateTooltip('d/M/Y')
                    ->toggleable(true),
            ])
            ->filters([
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from_date'),
                        Forms\Components\DatePicker::make('to_date'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from_date'], fn($q) => $q->whereDate('sale_date', '>=', $data['from_date']))
                            ->when($data['to_date'], fn($q) => $q->whereDate('sale_date', '<=', $data['to_date']));
                    }),
                Tables\Filters\SelectFilter::make('customer')
                    ->preload()
                    ->searchable()
                    ->relationship('customer', titleAttribute: 'name'),
                Tables\Filters\SelectFilter::make('Seller')
                    ->preload()
                    ->searchable()
                    ->multiple()
<<<<<<< HEAD
                    ->relationship('user', 'name'),
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
=======
                    ->relationship('customer', 'name'),
                Tables\Filters\SelectFilter::make('has_discount')
                    ->label('Discount?')
                    ->options([
                        'yes' => 'Yes',
                        'no'  => 'No',
                    ])
                    ->query(function ($query, $data) {
                        if (!isset($data['value'])) return;

                        if ($data['value'] === 'yes') {
                            // Only include sales with discount (discount_id > 1)
                            $query->whereHas('saleItems', fn($q) => $q->where('discount_id', '>', 1));
                        } else {
                            // Only include sales without discount (discount_id = 1)
                            $query->whereHas('saleItems', fn($q) => $q->where('discount_id', '=', 1));
                        }
                    }),
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
                    }),

>>>>>>> 8c30c670a9ec1afb31c671cb61f24a17e45bfe73
            ])
            // ->filters([
            //     Tables\Filters\SelectFilter::make('customer')
            //         ->preload()
            //         ->searchable()
            //         ->multiple()
            //         ->relationship('customer', titleAttribute: 'name'),
            //     Tables\Filters\SelectFilter::make('Seller')
            //         ->preload()
            //         ->searchable()
            //         ->multiple()
            //         ->relationship('user', 'name'),
            //     // Tables\Filters\Filter::make('sale_date')
            //     //     ->form([
            //     //         DatePicker::make('from')
            //     //             ->label('From Date'),
            //     //         DatePicker::make('until')
            //     //             ->label('Until Date'),
            //     //     ])
            //     //     ->query(function (Builder $query, array $data): Builder {
            //     //         return $query
            //     //             ->when(
            //     //                 $data['from'],
            //     //                 fn(Builder $query, $date): Builder => $query->whereDate('sale_date', '>=', $date),
            //     //             )
            //     //             ->when(
            //     //                 $data['until'],
            //     //                 fn(Builder $query, $date): Builder => $query->whereDate('sale_date', '<=', $date),
            //     //             );
            //     //     }),
            // ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

            ])
            ->bulkActions([

                ExportBulkAction::make()
                    ->color('primary')
                    ->exporter(SaleExporter::class)
                    ->formats([
                        ExportFormat::Xlsx,
                    ]),
            ])
<<<<<<< HEAD
            // ->headerActions([
            //     ActionsExportAction::make()
            //         ->exporter(SaleExporter::class)
            //         ->formats([
            //             EnumsExportFormat::Xlsx
            //         ]),
            // ])
            // ->groupedBulkActions([
            //     Tables\Actions\DeleteBulkAction::make()
            // ])
            // ->groups([
            //     Tables\Grouping\Group::make('sale_date')
            //         ->label('Order Date')
            //         ->date()
            //         ->collapsible(),
            // ])
            ->defaultSort('created_at', 'desc');
=======
            ->headerActions([
                Tables\Actions\ExportAction::make()->exporter(SaleItemExporter::class),
                // Export CSV action, triggers Excel export
                Tables\Actions\Action::make('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(
                        fn(): \Symfony\Component\HttpFoundation\BinaryFileResponse =>
                        Excel::download(new SalesItemExport, 'Sale.csv', \Maatwebsite\Excel\Excel::CSV)
                    ),

                // Export XLSX action
                Tables\Actions\Action::make('Export XLSX')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(
                        fn(): \Symfony\Component\HttpFoundation\BinaryFileResponse =>
                        Excel::download(new SalesItemExport, 'Sale.xlsx')
                    ),
            ])

            ->defaultSort('sale_date', 'desc'); // Default newest sales first
>>>>>>> 8c30c670a9ec1afb31c671cb61f24a17e45bfe73
    }

    /**
     * Defines infolist details view schema to display sale information nicely
     */
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Sale Information section with ID, dates, customer, user, and notes
                \Filament\Infolists\Components\Section::make('Sale Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('id')
                                    ->label('Sale ID')
                                    ->badge()
                                    ->formatStateUsing(fn($state) => Util::formatSaleId($state))
                                    ->color('primary'),

                                TextEntry::make('sale_date')
                                    ->label('Sale Date')
                                    ->date('d/m/Y')
                                    ->icon('heroicon-o-calendar-days'),

                                TextEntry::make('created_at')
                                    ->label('Created')
                                    ->since()
                                    ->icon('heroicon-o-clock'),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextEntry::make('customer.name')
                                    ->label('Customer')
                                    ->icon('heroicon-o-user')
                                    ->badge()
                                    ->color('success')
                                    ->weight(FontWeight::SemiBold),
                                TextEntry::make('customer.phone')
                                    ->label('Phone Number')
                                    ->icon('heroicon-o-user')
                                    ->badge()
                                    ->color('success')
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
                                    ->extraAttributes(['class' => 'p-4 bg-gray-50 rounded-lg']),
                            ]),
                    ])
                    ->columns(1),

                // Sale Items section to display each item with details and calculations
                \Filament\Infolists\Components\Section::make('Sale Items')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->schema([
                                Split::make([
                                    Grid::make(5)
                                        ->schema([
                                            TextEntry::make('product.name')
                                                ->label('Product')
                                                ->weight(FontWeight::SemiBold)
                                                ->icon('heroicon-o-cube'),
                                            TextEntry::make('qty')
                                                ->label('Quantity')
                                                ->badge()
                                                ->color('primary'),
                                            TextEntry::make('discount')
                                                ->getStateUsing(fn($record) => $record->discount ?? 0)
                                                ->suffix('%')
                                                ->badge()
                                                ->color('primary'),
                                            TextEntry::make('unit_price')
                                                ->label('Unit Price')
                                                ->money('USD')
                                                ->icon('heroicon-o-currency-dollar'),

                                            TextEntry::make('discount.value')
                                                ->label('Discount')
                                                ->formatStateUsing(function ($state, $record) {
                                                    // Format discount with % if percent type, else dollar amount
                                                    if ($record->discount?->ispercent == '1') {
                                                        return $state . '%';
                                                    }
                                                    return '-$' . number_format($state, 2);
                                                }),

                                            TextEntry::make('discount.value')
                                                ->label('Total')
                                                ->money('USD')
                                                ->weight(FontWeight::Bold)
                                                // ->color('success')
                                                ->state(function ($record) {
<<<<<<< HEAD
                                                    return $record->subTotal();
=======
                                                    $qty = $record->qty ?? 0;
                                                    $unitPrice = $record->unit_price ?? 0;
                                                    $discountValue = $record->discount->value ?? 0;
                                                    $isPercent = $record->discount->ispercent ?? '0';
                                                    $subtotal = $qty * $unitPrice;

                                                    if ($isPercent == '1') {
                                                        return $subtotal - ($subtotal * ($discountValue / 100));
                                                    } else {
                                                        return $subtotal - ($discountValue * $qty);
                                                    }
>>>>>>> 8c30c670a9ec1afb31c671cb61f24a17e45bfe73
                                                }),
                                        ]),
                                ]),
                            ])
                            ->contained(false)
                            ->hiddenLabel(),

                        Grid::make(5)
                            ->schema([
<<<<<<< HEAD
                                TextEntry::make('d')
                                    ->label(''),
                                TextEntry::make('s')
                                    ->label(''),
                                TextEntry::make('x')
                                    ->label(''),
                                TextEntry::make('p')
                                    ->label(''),

=======
>>>>>>> 8c30c670a9ec1afb31c671cb61f24a17e45bfe73
                                TextEntry::make('total_amount')
                                    ->label('Sub Total')
                                    ->state(function ($record) {
<<<<<<< HEAD
                                        return $record->total_price;
=======
                                        // Sum all items subtotals after discount
                                        return $record->items->sum(function ($item) {
                                            $qty = $item->qty ?? 0;
                                            $unitPrice = $item->unit_price ?? 0;
                                            $discountValue = $item->discount->value ?? 0;
                                            $isPercent = $item->discount->ispercent ?? '0';

                                            $subtotal = $qty * $unitPrice;

                                            if ($isPercent == '1') {
                                                return $subtotal - ($subtotal * ($discountValue / 100));
                                            } else {
                                                return $subtotal - ($discountValue * $qty);
                                            }
                                        });
>>>>>>> 8c30c670a9ec1afb31c671cb61f24a17e45bfe73
                                    })
                                    ->money('USD')
                                    ->size('lg')
                                    ->weight(FontWeight::Bold)
                                    ->color('success')
                                    ->icon('heroicon-o-currency-dollar'),
                            ]),
                    ])
                    ->collapsible(),

                // Print receipt button aligned right
                Grid::make()
                    ->schema([
                        TextEntry::make('print_button')
                            ->label('')
                            ->html()
                            ->state(function ($record) {
                                return '<div style="text-align: right;">
                                    <a href="' . route('receipt.print', ['sale' => $record->id]) . '" target="_blank"
                                        style="background-color:rgb(43, 179, 64); color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; display: inline-block;">
                                        🖨️ Print Receipt
                                    </a>
                                </div>';
                            })
                            ->columnSpanFull(),
                    ]),
            ]);
    }

<<<<<<< HEAD
=======
    /**
     * No custom relations defined currently
     */
>>>>>>> 8c30c670a9ec1afb31c671cb61f24a17e45bfe73
    public static function getRelations(): array
    {
        return [];
    }

    /**
     * Define page routes and their classes
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
<<<<<<< HEAD
            // 'view' => Pages\ViewSale::route('/{record}'),
            // 'edit' => Pages\EditSale::route('/{record}/edit'),
=======
            'view2' => Pages\ViewSale::route('/{record}'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
>>>>>>> 8c30c670a9ec1afb31c671cb61f24a17e45bfe73
        ];
    }

    /**
     * Define any widgets shown in the resource view
     */
    public static function getWidgets(): array
    {
        return [
            Salestats::class,
        ];
    }
}
