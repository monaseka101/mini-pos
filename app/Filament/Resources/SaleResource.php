<?php

namespace App\Filament\Resources;

use App\Filament\Exports\DetailedSaleExporter;
use App\Filament\Exports\SaleExporter;
use App\Filament\Exports\SaleItemExporter;
use App\Filament\Pages\ShopPage;
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
use Filament\Tables\Filters\Filter;
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
                            ->schema([
                                static::getItemsRepeater(),
                            ]),
                    ])
                    ->columnSpan(['lg' => fn(?Sale $record) => $record === null ? 3 : 2]),
            ]);
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
            Forms\Components\DatePicker::make('sale_date'),
            // ->columnSpan('full'),

            Forms\Components\RichEditor::make('note')
                ->columnSpan('full'),
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
                    ->searchable(),
                Forms\Components\TextInput::make('qty')
                    ->label('Quantity')
                    ->numeric()
                    ->extraAttributes([
                        'onkeydown' => "if(['e','E','+','-'].includes(event.key)) event.preventDefault();",
                    ])
                    ->default(1)
                    ->minValue(1)
                    ->maxValue(fn(Get $get) => $get('available_stock'))
                    ->validationMessages([
                        'max' => 'The product in stock have only :max.',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('discount')
                    ->default(0),
                Forms\Components\TextInput::make('available_stock')
                    ->label('In Stock')
                    ->disabled()
                    ->dehydrated(false),

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
            // SaleStats::class
        ];
    }


    // Table Display
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
                    ->sortable(),
                // ->url(
                //     fn($record) => CustomerResource::getUrl('customer.view', ['record' => $record->customer_id ?? null]),
                //     shouldOpenInNewTab: true
                // ),

                Tables\Columns\TextColumn::make('products')
                    ->label('Products')
                    ->wrap()
                    ->getStateUsing(fn(Sale $record) => $record->listProducts()),

                Tables\Columns\TextColumn::make('total_qty')
                    ->label('Total Qty')
                    ->weight(FontWeight::Bold)
                    ->getStateUsing(fn(Sale $record) => $record->total_qty),

                Tables\Columns\TextColumn::make('total_price')
                    // ->sortable()
                    ->money(currency: 'usd')
                    ->weight(FontWeight::Bold)
                    ->sortable(query: fn(Builder $query, string $direction) => Sale::sortByTotalPrice($query, $direction))
                    ->formatStateUsing(fn($record) => '$' . number_format($record->items->sum(fn($item) => $item->qty * $item->unit_price), 2))
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
                Filter::make('sale_date')
                    ->form([
                        DatePicker::make('sale_from')
                            ->label('Sale From'),
                        DatePicker::make('sale_until')
                            ->label('Sale Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['sale_from'],
                                callback: fn(Builder $query, $date): Builder => $query->whereDate('sale_date', '>=', $date),
                            )
                            ->when(
                                $data['sale_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('sale_date', '<=', $date),
                            );
                    }),

                Tables\Filters\SelectFilter::make('customer')
                    ->preload()
                    ->searchable()
                    ->relationship('customer', titleAttribute: 'name'),
                // Filter by total price 
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

            ->actions([
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Sale $record) {
                        Log::info($record);
                        foreach ($record->items as $item) {
                            $product = $item->product;
                            $product->increment('stock', $item->qty);
                        }
                    })
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

    // InfoList Display
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
                                    Grid::make(7)
                                        ->schema([
                                            TextEntry::make('product.name')
                                                ->label('Product')
                                                ->weight(FontWeight::SemiBold)
                                                ->icon('heroicon-o-cube'),
                                            TextEntry::make('qty')
                                                ->label('Quantity')
                                                ->badge()
                                                ->color('primary'),
                                            TextEntry::make('product.brand.name')
                                                ->label('Brand')
                                                ->badge()
                                                ->color('success'),
                                            TextEntry::make('product.category.name')
                                                ->label('Category')
                                                ->badge()
                                                ->color('success'),
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
            'list' => Pages\CustomListSale::route('/custom-list'),
            'create' => Pages\CreateSale::route('/create'),
            'invoice' => Pages\SaleInvoice::route('/{record}/invoice'),
            // 'view' => Pages\ViewSale::route('/{record}'),
            // 'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }
}
