<?php

namespace App\Filament\Resources\BrandResource\RelationManagers;

use App\Helpers\Util;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Product Information')
                    ->description('Basic product details')
                    ->icon('heroicon-m-information-circle')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Product Name')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('Enter product name')
                                ->unique(ignoreRecord: true),

                            Forms\Components\TextInput::make('price')
                                ->label('Price')
                                ->required()
                                ->numeric()
                                ->extraAttributes([
                                    'onkeydown' => "if(['e','E','+','-'].includes(event.key)) event.preventDefault();",
                                ])
                                ->prefix('$')
                                ->placeholder('0.00')
                                ->minValue(0.01),

                            Forms\Components\Select::make('category_id')
                                ->label('Category')
                                ->required()
                                ->relationship('category', 'name', fn(Builder $query) => $query->where('active', true))
                                ->searchable()
                                ->preload()
                                ->exists(table: Category::class, column: 'id')
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('name')
                                        ->unique()
                                        ->required()
                                        ->maxLength(255),
                                ])
                                ->placeholder('Select or create category'),

                            Forms\Components\TextInput::make('stock_security')
                                ->label('Stock Security')
                                ->required()
                                ->numeric()
                                ->extraAttributes([
                                    'onkeydown' => "if(['e','E','+','-'].includes(event.key)) event.preventDefault();",
                                ])
                                ->minValue(1)
                                ->helperText('Minimum stock level before warning'),
                        ]),

                        Forms\Components\RichEditor::make('description')
                            ->label('Product Description')
                            ->placeholder('Describe your product in detail...')
                            ->columnSpanFull(),
                    ]),

                Section::make('Product Media')
                    ->description('Upload product images')
                    ->icon('heroicon-m-photo')
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->label('Product Image')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios(['16:9', '4:3', '1:1'])
                            ->directory('products')
                            ->disk('public')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->helperText('Upload a high quality product image (max 2MB, JPG/PNG/WebP)')
                            ->columnSpanFull(),
                    ]),

                Section::make('Status')
                    // ->description('Product availability')
                    ->icon('heroicon-m-eye')
                    ->schema([
                        Forms\Components\Toggle::make('active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Toggle to activate/deactivate this product'),

                        Forms\Components\Hidden::make('user_id')
                            ->default(auth()->id())
                            ->dehydrated(fn($state) => filled($state)),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->size(60)
                    ->defaultImageUrl(fn($record) => Util::getDefaultAvatar($record->name)),

                Tables\Columns\TextColumn::make('name')
                    ->label('Product Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money()
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock')
                    ->sortable()
                    ->badge()
                    ->color(fn($record) => match (true) {
                        $record->stock <= 0 => 'danger',
                        $record->stock <= $record->stock_security => 'warning',
                        default => 'success'
                    })
                    ->icon(fn($record) => match (true) {
                        $record->stock <= 0 => 'heroicon-m-x-circle',
                        $record->stock <= $record->stock_security => 'heroicon-m-exclamation-triangle',
                        default => 'heroicon-m-check-circle'
                    }),

                Tables\Columns\IconColumn::make('active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-m-check-circle')
                    ->falseIcon('heroicon-m-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->preload()
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('active')
                    ->label('Status')
                    ->placeholder('All products')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Product')
                    ->icon('heroicon-m-plus'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-m-pencil-square'),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('activate')
                    ->label('Activate Selected')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn(Collection $records) => $records->each->update(['active' => true])),

                Tables\Actions\BulkAction::make('deactivate')
                    ->label('Deactivate Selected')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn(Collection $records) => $records->each->update(['active' => false])),
            ]);
    }
}
