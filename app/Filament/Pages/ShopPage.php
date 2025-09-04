<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Columns;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class ShopPage extends Page implements Tables\Contracts\HasTable, Forms\Contracts\HasForms
{
    use Tables\Concerns\InteractsWithTable;
    use Forms\Concerns\InteractsWithForms;

    protected static string $view = 'filament.pages.shop-page';
    protected static ?string $title = '';
    // protected static ?string $navigationGroup = 'Sales';
    // Disable the title of the table

    protected static bool $shouldRegisterNavigation = false;

    // formData used to store cart items to sale
    public ?array $formData = [
        'items' => [],
    ];

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Repeater::make('items')
                    ->reorderable(false)
                    ->hiddenLabel()
                    ->schema([
                        Hidden::make('product_id'),
                        TextInput::make('name')->disabled(),
                        TextInput::make('qty')
                            ->type('number')
                            ->default(1)
                            ->minValue(1)
                            ->numeric()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn() => $this->updateTotals()),
                        TextInput::make('stock')
                            ->label('In Stock')
                            ->disabled(),
                        TextInput::make('unit_price')
                            ->label('Unit Price')
                            ->prefix('$')
                            ->numeric()
                            ->step(0.01)
                            ->disabled(),
                        TextInput::make('discount')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn() => $this->updateTotals()),

                        // TextInput::make('subtotal')
                        //     ->disabled()
                        //     ->formatStateUsing(fn($state) => '$' . number_format($state ?? 0, 2)),
                    ])
                    ->columns(5)
                    ->default([])
                    ->addable(false)
                    ->deletable(true)
                    ->deleteAction(
                        fn($action) => $action->after(fn() => $this->updateTotals())
                    ),
                Forms\Components\Select::make('customer_id')
                    ->label('Customer')
                    ->options(\App\Models\Customer::pluck('name', 'id'))
                    ->searchable()
                    ->visible(fn(callable $get) => !empty($get('items')))
                    ->required(),
            ])
            ->statePath('formData')
            ->model(null)
            ->extraAttributes(['class' => 'space-y-4']);
    }

    public function updateTotals(): void
    {
        $items = $this->formData['items'] ?? [];

        foreach ($items as &$item) {
            $qty = (int) ($item['qty'] ?? 1);
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $discount = (int) ($item['discount'] ?? 0);

            $subtotal = $qty * $unitPrice;
            $discountAmount = $subtotal * ($discount / 100);
            $item['subtotal'] = $subtotal - $discountAmount;
        }

        $this->formData['items'] = $items;
    }

    public function getTotalAmount(): float
    {
        $items = $this->formData['items'] ?? [];
        return collect($items)->sum('subtotal');
    }

    public function checkout(): void
    {
        $items = $this->formData['items'] ?? [];
        // $customerId = $this->formData['customer_id'] ?? null;

        if (empty($items)) {
            Notification::make()
                ->title('Cart is empty!')
                ->danger()
                ->send();
            return;
        }

        // if (!$customerId) {
        //     Notification::make()
        //         ->title('Please select a customer!')
        //         ->danger()
        //         ->send();
        //     return;
        // }


        try {
            DB::transaction(function () use ($items) {
                // Create the main sale record
                $sale = Sale::create([
                    'user_id' => auth()->id(),
                    'sale_date' => now(),
                    // 'total_amount' => $this->getTotalAmount(),
                    // Add other sale fields as needed
                ]);

                // Create sale items
                foreach ($items as $item) {
                    // Validate stock availability
                    $product = Product::find($item['product_id']);
                    if (!$product || $product->stock < $item['qty']) {
                        throw new \Exception("Insufficient stock for {$item['name']}");
                    }

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $item['product_id'],
                        'qty' => (int) $item['qty'],
                        'unit_price' => (float) $item['unit_price'],
                        'discount' => (int) ($item['discount'] ?? 0),
                    ]);

                    // Update product stock
                    $product->decrement('stock', $item['qty']);
                }
            });

            // Clear cart after successful checkout
            $this->formData['items'] = [];

            Notification::make()
                ->title('Sale successful!')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Log::error('Sale failed: ' . $e->getMessage());

            Notification::make()
                ->title('Sale failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    // public function table(Table $table): Table
    // {
    //     return $table
    //         ->query(Product::query())
    //         ->columns([
    //             Stack::make([
    //                 ImageColumn::make('image')
    //                     ->height(200)
    //                     ->defaultImageUrl(fn($record) => \App\Helpers\Util::getDefaultAvatar($record->name)),
    //                 TextColumn::make('name')
    //                     ->searchable()
    //                     ->weight('bold')
    //                     ->size('lg'),
    //                 TextColumn::make('price')
    //                     ->formatStateUsing(fn($state) => '$' . number_format($state, 2)),
    //                 // TextColumn::make('stock')
    //                 //     ->formatStateUsing(fn($state) => "In Stock: {$state}"),
    //             ])
    //         ])
    //         ->actions([
    //             Tables\Actions\Action::make('addToCart')
    //                 ->label('Add to Sale')
    //                 ->button()
    //                 ->color('primary')
    //                 ->action(fn($record) => $this->addToCart($record)),
    //         ])
    //         ->contentGrid([
    //             'sm' => 1,
    //             'md' => 2,
    //             'lg' => 2,
    //             'xl' => 3,
    //         ])
    //         ->recordUrl(null);
    // }

    public function table(Table $table): Table
    {
        return $table
            ->header(null)
            // ->query(Product::query())
            ->query(Product::query()->where('active', 1)->where('stock', '>', 0))
            ->defaultSort('stock', 'desc')
            ->columns([
                Stack::make([
                    ImageColumn::make('image')
                        ->height(50)
                        // ->extraImgAttributes(['class' => 'rounded-xl object-cover'])
                        ->defaultImageUrl(fn($record) => \App\Helpers\Util::getDefaultAvatar($record->name)),

                    TextColumn::make('name')
                        ->searchable()
                        ->weight('bold')
                        ->size('md')
                        ->limit(30),

                    TextColumn::make('description')
                        ->html()
                        ->limit(50)
                        ->color('gray')
                        ->wrap(),
                    TextColumn::make('price')
                        ->formatStateUsing(fn($state) => '$' . number_format($state, 2))
                        ->color('success')
                        ->weight('bold')
                        ->size('md'),

                    TextColumn::make('stock')
                        ->badge()
                        ->color(fn($state) => $state > 0 ? 'success' : 'danger')
                        ->formatStateUsing(fn($state) => $state > 0 ? "In Stock: {$state}" : 'Out of Stock'),
                ])
            ])
            ->actions([
                Tables\Actions\Action::make('addToCart')
                    ->label('Add to Sale')
                    ->icon('heroicon-o-shopping-cart')
                    ->button()
                    ->color('primary')
                    // ->visible(fn($record) => $record->stock > 0)
                    ->action(fn($record) => $this->addToCart($record)),
            ])
            ->contentGrid([
                'sm' => 1,
                'md' => 2,
                'lg' => 2,
                'xl' => 3,
            ])
            // ->paginationPageOptions([6, 12, 24])
            // ->defaultPaginationPageOption(6)
            ->paginated([6])
            ->recordUrl(null);
    }



    public function addToCart(Product $product): void
    {
        $items = $this->formData['items'] ?? [];
        Log::info($this->formData);

        // Check if product already exists in cart
        $existingIndex = collect($items)->search(fn($item) => $item['product_id'] === $product->id);

        if ($existingIndex !== false) {
            // Increase qty for existing item
            $items[$existingIndex]['qty'] = ($items[$existingIndex]['qty'] ?? 1) + 1;
        } else {
            // Add new item to cart
            $items[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'stock' => $product->stock,
                'qty' => 1,
                'unit_price' => $product->price,
                'discount' => 0,
                'subtotal' => $product->price,
            ];
        }

        $this->formData['items'] = $items;
        $this->updateTotals();

        Notification::make()
            ->title('Product added')
            ->seconds(2)
            ->body("{$product->name} has been added to sale")
            ->success()
            ->send();
    }

    public function removeFromCart(int $index): void
    {
        $items = $this->formData['items'] ?? [];

        if (isset($items[$index])) {
            unset($items[$index]);
            $this->formData['items'] = array_values($items); // Re-index array
            $this->updateTotals();

            Notification::make()
                ->title('Item removed')
                ->body('Item has been removed from your cart')
                ->success()
                ->send();
        }
    }
}
