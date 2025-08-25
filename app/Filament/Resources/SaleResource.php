<?php

namespace App\Filament\Resources;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use App\Filament\Resources\SaleResource\Pages;
use App\Helpers\Util;
use Filament\Actions\Action;
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
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesItemExport;
use App\Filament\Resources\SaleResource\Widgets\Salestats;

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

    /**
     * Returns form components for the Sale details section
     */
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
                    ->relationship('product', 'name', fn($query) => $query->where('active', true))
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

                // Stock display (disabled, not submitted)
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

    /**
     * Defines the table listing sales with sorting, filtering, and actions
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label("Id")
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable()
                    // Link to the customer view page, opens in new tab
                    ->url(
                        fn($record) => CustomerResource::getUrl('customer.view', ['record' => $record->customer_id]),
                        shouldOpenInNewTab: true
                    ),

                Tables\Columns\TextColumn::make('note')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->html(),

                Tables\Columns\TextColumn::make('total_price')
                    ->money(currency: 'usd')
                    ->getStateUsing(fn(Sale $record) => $record->totalPrice()) // Sum price before discount
                    ->sortable()
                    ->badge()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('has_discount')
                    ->label('Discount?')
                    ->getStateUsing(fn(Sale $record) => $record->getHasDiscountAttribute())
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_pay')
                    ->money(currency: 'usd')
                    ->getStateUsing(fn(Sale $record) => $record->totalPay()) // Sum price after discount
                    ->sortable()
                    ->badge()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('sale_date')
                    ->label('Sale Date')
                    ->sortable()
                    ->dateTime('d/m/Y')
                    ->dateTooltip('d/M/Y')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('customer')
                    ->preload()
                    ->searchable()
                    ->multiple()
                    ->relationship('customer', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->groupedBulkActions([
                Tables\Actions\DeleteBulkAction::make()
            ])
            ->headerActions([
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
                                                ->color('info'),

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
                                                ->color('success')
                                                ->state(function ($record) {
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
                                                }),
                                        ]),
                                ]),
                            ])
                            ->contained(false)
                            ->hiddenLabel(),

                        Grid::make(4)
                            ->schema([
                                TextEntry::make('total_amount')
                                    ->label('Sub Total')
                                    ->state(function ($record) {
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

    /**
     * No custom relations defined currently
     */
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
            'view2' => Pages\ViewSale::route('/{record}'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
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
