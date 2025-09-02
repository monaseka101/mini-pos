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
use App\Models\SaleItem;
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
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Split;
use Filament\Support\Enums\FontWeight;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Infolist;
use Filament\Tables\Actions\ExportAction as ActionsExportAction;
use Filament\Tables\Actions\ExportBulkAction;
use Illuminate\Support\Facades\Log;
use OpenSpout\Common\Entity\Style\CellAlignment;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;
    protected static ?string $navigationIcon = 'heroicon-m-shopping-cart';
    protected static ?string $navigationLabel = 'Sales';
    protected static ?string $modelLabel = 'Sale';
    protected static ?string $pluralModelLabel = 'Sales';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema(static::getDetailsFormSchema())
                            ->columns(2),

                        Forms\Components\Section::make('Sale Items')
                            // ->headerActions([
                            //     Action::make('reset')
                            //         ->modalHeading('Are you sure?')
                            //         ->modalDescription('All existing items will be removed from the order.')
                            //         ->requiresConfirmation()
                            //         ->color('danger')
                            //         ->action(fn(Forms\Set $set) => $set('items', [])),
                            // ])
                            ->schema([
                                static::getItemsRepeater(),
                            ]),
                    ])
                    ->columnSpan(['lg' => fn(?Sale $record) => $record === null ? 3 : 2]),

                // Forms\Components\Section::make()
                //     ->schema([
                //         Forms\Components\Placeholder::make('created_at')
                //             ->label('Created at')
                //             ->content(fn(Sale $record): ?string => $record->created_at?->diffForHumans()),

                //         Forms\Components\Placeholder::make('updated_at')
                //             ->label('Last modified at')
                //             ->content(fn(Sale $record): ?string => $record->updated_at?->diffForHumans()),
                //     ])
                //     ->columnSpan(['lg' => 1])
                //     ->hidden(fn(?Sale $record) => $record === null),
            ]);
        // ->columns(4);
    }

    public static function getDetailsFormSchema(): array
    {
        return [
            Forms\Components\Select::make('customer_id')
                ->relationship(
                    'customer',
                    'name',
                    fn($query) => $query->where('active', true)
                )
                ->preload()
                ->searchable()
                ->required()
                ->createOptionForm([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('phone')
                        ->maxLength(255),
                ]),

            Forms\Components\DatePicker::make('sale_date')
                // ->date()
                // ->displayFormat(function () {
                //     return 'd/m/Y';
                // })
                ->default(now()),

            Forms\Components\RichEditor::make('note')
                ->columnSpan('full'),
            // Forms\Components\Toggle::make('active')
            //     ->default(true)
            //     ->required(),
        ];
    }

    public static function getItemsRepeater(): Repeater
    {
        return Repeater::make('items')
            ->relationship()
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'name', fn(Builder $query) => $query->where('active', true)->where('stock', '>', 0))
                    ->preload()
                    ->required()
                    // ->reactive()
                    ->afterStateUpdated(
                        function ($state, Forms\Set $set, Forms\Get $get) {
                            $product = Product::find($state);
                            if ($product) {
                                $set('unit_price', $product->price ?? 0);
                                $set('available_stock', $product->stock ?? 0);
                            }
                        }
                    )
                    ->distinct()
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                    // ->columnSpan([
                    //     'md' => 2,
                    // ])
                    ->searchable(),
                Forms\Components\TextInput::make('qty')
                    ->label('Quantity')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->maxValue(fn(Get $get) => $get('available_stock'))
                    ->validationMessages([
                        'max' => 'The product in stock have only :max.',
                    ])
                    // ->columnSpan([
                    //     'md' => 2,
                    // ])
                    ->required(),

                Forms\Components\TextInput::make('discount')
                    ->default(0),
                Forms\Components\TextInput::make('available_stock')
                    ->label('In Stock')
                    ->disabled()
                    ->dehydrated(false),
                // ->columnSpan([
                //     'md' => 2,
                // ]),

                Forms\Components\TextInput::make('unit_price')
                    ->label('Unit Price')
                    ->disabled()
                    ->dehydrated()
                    ->prefix('$')
                    ->required(),
            ])
            ->orderColumn('')
            ->defaultItems(1)
            ->hiddenLabel()
            ->columns(5)
            ->required();
    }

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
                    ->url(
                        fn($record) => CustomerResource::getUrl('customer.view', ['record' => $record->customer_id]),
                        shouldOpenInNewTab: true
                    ),

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
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
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
                                    ->extraAttributes([
                                        'class' => 'p-4 bg-gray-50 rounded-lg',
                                    ])
                            ])

                    ])
                    ->columns(1),

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

                                            TextEntry::make('sub_total')
                                                ->label('Sub Total')
                                                ->money('USD')
                                                ->weight(FontWeight::Bold)
                                                // ->color('success')
                                                ->state(function ($record) {
                                                    return $record->subTotal();
                                                }),
                                        ]),
                                ])
                            ])
                            ->contained(false)
                            ->hiddenLabel(),

                        Grid::make(5)
                            ->schema([
                                TextEntry::make('d')
                                    ->label(''),
                                TextEntry::make('s')
                                    ->label(''),
                                TextEntry::make('x')
                                    ->label(''),
                                TextEntry::make('p')
                                    ->label(''),

                                TextEntry::make('total_amount')
                                    ->label('Total Amount')
                                    ->state(function ($record) {
                                        return $record->total_price;
                                    })
                                    ->money('USD')
                                    ->size('lg')
                                    ->weight(FontWeight::Bold)
                                    ->color('success')
                                    ->icon('heroicon-o-currency-dollar'),
                            ])
                    ])
                    ->collapsible(),
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
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            // 'view' => Pages\ViewSale::route('/{record}'),
            // 'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }
}
