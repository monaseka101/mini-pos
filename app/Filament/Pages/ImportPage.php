<?php


namespace App\Filament\Pages;


use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\ProductImport;
use App\Models\ProductImportItem;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
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
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;


class ImportPage extends Page implements Tables\Contracts\HasTable, Forms\Contracts\HasForms
{
    use Tables\Concerns\InteractsWithTable;
    use Forms\Concerns\InteractsWithForms;


    protected static string $view = 'filament.pages.import-page';
    protected static ?string $title = '';


    protected static bool $shouldRegisterNavigation = false;


    // formData used to store cart items and customer for sale
    public ?array $formData = [
        'items' => [],
        'supplier_id' => null,
    ];


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Customer selection - always visible and required when items exist
                Forms\Components\Select::make('supplier_id')
                    ->label('Select Supplier')
                    ->options(\App\Models\Supplier::pluck('name', 'id'))
                    ->searchable()
                    ->placeholder('Select the supplier')
                    ->visible(fn(callable $get) => !empty($get('items')))
                    ->required(fn(callable $get) => !empty($get('items')))
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('note')
                    ->label('Note')
                    ->columnSpanFull()
                    ->visible(fn(callable $get) => !empty($get('items')))
                    ->nullable(),

                Repeater::make('items')
                    ->reorderable(false)
                    ->hiddenLabel()
                    ->schema([
                        Hidden::make('product_id'),
                        TextInput::make('name')->disabled(),
                        TextInput::make('qty')
                            ->label('Quantity')
                            ->type('number')
                            ->default(1)
                            ->minValue(1)
                            ->numeric()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn() => $this->updateTotals()),
                        TextInput::make('stock')
                            ->label('Currently In Stock')
                            ->disabled(),
                        TextInput::make('unit_price')
                            ->label('Per Unit Price')
                            ->prefix('$')
                            ->numeric()
                            ->required(),
                        TextInput::make('product_price')
                            ->label('Current Selling Price')
                            ->disabled()
                            ->dehydrated(false) // prevents it from being saved to DB
                            ->prefix('$')


                    ])
                    ->columns(5)
                    ->default([])
                    ->addable(false)
                    ->deletable(true)
                    ->deleteAction(
                        fn($action) => $action->after(fn() => $this->updateTotals())
                    ),
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
            $product = \App\Models\Product::find($item['product_id']);

            // set current product price (live from DB)
            $item['product_price'] = $product?->price ?? 0;
            $subtotal = $qty * $unitPrice;
            $item['subtotal'] = $subtotal;
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
        $supplierId = $this->formData['supplier_id'] ?? null;


        // Validate cart is not empty
        if (empty($items)) {
            Notification::make()
                ->title('Cart is empty!')
                ->body('Please add some items to your cart before checking out.')
                ->danger()
                ->send();
            return;
        }

        // Validate customer is selected
        if (!$supplierId) {
            Notification::make()
                ->title('Please select The Supplier!')
                ->body('A Supplier must be selected to complete the Import.')
                ->danger()
                ->send();
            return;
        }

        try {
            DB::transaction(function () use ($items, $supplierId) {
                // Create the main sale record with proper customer_id
                $product_import = ProductImport::create([
                    'user_id' => Auth::id(),
                    'supplier_id' => $supplierId,
                    'import_date' => now(),
                    'note' => $this->formData['note'] ?? null,
                    // 'total_amount' => $this->getTotalAmount(),
                ]);


                // Create sale items
                foreach ($items as $item) {
                    // Validate stock availability
                    $product = Product::find($item['product_id']);
                    /* if (!$product || $product->stock < $item['qty']) {
                        throw new \Exception("Insufficient stock for {$item['name']}. Available: {$product->stock}, Required: {$item['qty']}");
                    }  */


                    ProductImportItem::create([
                        'product_import_id' => $product_import->id,
                        'product_id' => $item['product_id'],
                        'qty' => (int) $item['qty'],
                        'unit_price' => (float) $item['unit_price'],

                    ]);

                    // Update product stock
                    $product->increment('stock', $item['qty']);
                }
            });


            // Clear cart and customer selection after successful checkout
            $this->formData['items'] = [];
            $this->formData['supplier_id'] = null;


            Notification::make()
                ->title('Import completed successfully!')
                ->body('Import has been processed and inventory updated.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Log::error('Import failed: ' . $e->getMessage());


            Notification::make()
                ->title('Import failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }


    public function table(Table $table): Table
    {
        return $table
            ->header(null)
            ->query(Product::query()->where('active', 1))
            ->defaultSort('stock')
            ->columns([
                Stack::make([
                    ImageColumn::make('image')
                        ->height(100)
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
                    // TextColumn::make('brand.name')
                    //     ->searchable()
                    //     ->label('Brand')
                    //     ->badge()
                    //     ->color(color: 'info'),
                    // TextColumn::make('category.name')
                    //     ->badge()
                    //     ->color('info')
                    //     ->searchable(),


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
                    ->label('Add to Import')
                    ->icon('heroicon-s-arrow-down-tray')
                    ->button()
                    ->color('primary')
                    ->action(fn($record) => $this->addToCart($record)),
            ])
            ->contentGrid([
                'sm' => 1,
                'md' => 2,
                'lg' => 2,
                'xl' => 3,
            ])
            ->paginated([6])
            ->recordUrl(null);
    }


    public function addToCart(Product $product): void
    {
        $items = $this->formData['items'] ?? [];
        Log::info('Adding to cart:', $this->formData);


        // Check if product already exists in cart
        $existingIndex = collect($items)->search(fn($item) => $item['product_id'] === $product->id);


        if ($existingIndex !== false) {
            // Check stock before increasing quantity
            $newQty = ($items[$existingIndex]['qty'] ?? 1) + 1;
            /* if ($newQty > $product->stock) {
                Notification::make()
                    ->title('Insufficient stock!')
                    ->body("Only {$product->stock} units available for {$product->name}")
                    ->warning()
                    ->send();
                return;
            } */


            $items[$existingIndex]['qty'] = $newQty;
        } else {
            // Add new item to cart
            $items[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'stock' => $product->stock,
                'qty' => 1,
                'unit_price' => 0,

            ];
        }

        $this->formData['items'] = $items;
        $this->updateTotals();

        Notification::make()
            ->title('Product added')
            ->seconds(2)
            ->body("{$product->name} has been added to import")
            ->success()
            ->send();
    }

    public function removeFromCart(int $index): void
    {
        $items = $this->formData['items'] ?? [];

        if (isset($items[$index])) {
            $productName = $items[$index]['name'] ?? 'Item';
            unset($items[$index]);
            $this->formData['items'] = array_values($items); // Re-index array
            $this->updateTotals();


            Notification::make()
                ->title('Item removed')
                ->body("{$productName} has been removed from your cart")
                ->success()
                ->send();
        }
    }
}
