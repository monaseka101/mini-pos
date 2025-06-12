<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Filament\Resources\ProductResource\Widgets\ProductStats;
use App\Helpers\Util;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Grid as InfoGrid;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Forms\Components\Grid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Split;
use Filament\Support\Enums\FontWeight;

use function Laravel\Prompts\table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-m-squares-2x2';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationGroup = 'Inventory';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Product Information')
                    ->icon('heroicon-m-information-circle')
                    ->schema([
                        Grid::make(2)
                            ->schema([
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
                                            ->required()
                                            ->maxLength(255),
                                    ])
                                    ->placeholder('Select or create category'),

                                Forms\Components\Select::make('brand_id')
                                    ->label('Brand')
                                    ->required()
                                    ->relationship('brand', 'name', fn(Builder $query) => $query->where('active', true))
                                    ->searchable()
                                    ->preload()
                                    ->exists(table: Brand::class, column: 'id')
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->maxLength(255),
                                    ])
                                    ->placeholder('Select or create brand'),
                                Forms\Components\TextInput::make('stock_security')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)

                            ]),

                        Forms\Components\RichEditor::make('description')
                            ->label('Product Description')
                            ->placeholder('Describe your product in detail...')
                            ->columnSpanFull(),

                    ]),

                // Media Section
                Section::make('Product Media')
                    ->description('Upload product images')
                    ->icon('heroicon-m-photo')
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->label('Product Image')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->directory('products')
                            ->disk('public')
                            ->visibility('public')
                            ->maxSize(2048) // 2MB
                            ->helperText('Upload a f-quality product image (max 2MB, JPG/PNG/WebP)')
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Toggle::make('active')
                    ->default(true)
                    ->required(),

            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\ImageColumn::make('image')
                    ->defaultImageUrl(fn($record) => Util::getDefaultAvatar($record->name)),
                Tables\Columns\TextColumn::make('price')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->badge()
                    ->color(
                        fn($record) =>
                        $record->stock <= 0 ? 'danger' : ($record->stock <= $record->stock_security ? 'warning' : 'success')
                    )
                    ->icon(
                        fn($record) =>
                        $record->stock <= 0 ? 'heroicon-m-x-circle' : ($record->stock <= $record->stock_security ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                    ),

                Tables\Columns\TextColumn::make('brand.name')
                    ->label('Brand')
                    ->badge()
                    ->color(color: 'gray'),
                Tables\Columns\TextColumn::make('category.name')
                    ->badge()
                    ->color('info'),
                Tables\Columns\IconColumn::make('active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('user.name')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Created By'),
                // ->url(
                //     fn ($record) => $record->user ? route('filament.admin.resources.users.view', ['record' => $record->user]) : null,
                //     shouldOpenInNewTab: true
                // ),
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
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->preload()
                    ->multiple(),
                Tables\Filters\SelectFilter::make('brand')
                    ->relationship('brand', 'name')
                    ->preload()
                    ->multiple(),
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Status')
                    ->placeholder('All Products')
                    ->trueLabel('Active Products')
                    ->falseLabel('Inactive Products')
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->action(fn(Collection $records) => $records->each->update(['active' => true])),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->action(fn(Collection $records) => $records->each->update(['active' => false])),
                ]),
            ])
            ->defaultSort('active', 'desc');
    }

    public static function getWidgets(): array
    {
        return [
            ProductStats::class
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Split::make([
                    // Left Column - Main Product Info
                    InfoSection::make('Product Overview')
                        ->icon('heroicon-m-cube')
                        ->schema([
                            InfoGrid::make(3)
                                ->schema([
                                    ImageEntry::make('image')
                                        ->label('Product Image')
                                        ->height(150)
                                        ->width(150)
                                        ->defaultImageUrl(url('/images/placeholder-product.png'))
                                        ->columnSpan(1)
                                        ->extraImgAttributes(['class' => 'rounded-lg shadow-sm']),

                                    InfoGrid::make(1)
                                        ->schema([
                                            TextEntry::make('name')
                                                ->label('Product Name')
                                                ->size(TextEntry\TextEntrySize::Large)
                                                ->weight(FontWeight::Bold)
                                                ->copyable()
                                                ->copyMessage('Product name copied')
                                                ->copyMessageDuration(1500),

                                            TextEntry::make('description')
                                                ->label('Description')
                                                ->html()
                                                ->placeholder('No description provided')
                                                ->limit(200)
                                                ->tooltip(fn($state) => strlen($state) > 200 ? $state : null),
                                        ])
                                        ->columnSpan(2),
                                ]),
                        ]),

                    // Right Column - Quick Stats
                    InfoSection::make('Quick Stats')
                        ->icon('heroicon-m-chart-bar')
                        ->schema([
                            InfoGrid::make(2)
                                ->schema([
                                    TextEntry::make('price')
                                        ->label('Price')
                                        ->money('USD')
                                        ->size(TextEntry\TextEntrySize::Large)
                                        ->weight(FontWeight::Bold)
                                        ->color('success')
                                        ->icon('heroicon-m-currency-dollar'),

                                    TextEntry::make('stock')
                                        ->label('Stock Level')
                                        ->numeric()
                                        ->badge()
                                        ->color(fn($state) => match (true) {
                                            $state === 0 => 'danger',
                                            $state <= 10 => 'warning',
                                            default => 'success',
                                        })
                                        ->formatStateUsing(fn($state) => $state . ' units')
                                        ->icon(fn($state) => match (true) {
                                            $state === 0 => 'heroicon-m-x-circle',
                                            $state <= 10 => 'heroicon-m-exclamation-triangle',
                                            default => 'heroicon-m-check-circle',
                                        }),

                                    IconEntry::make('active')
                                        ->label('Status')
                                        ->boolean()
                                        ->trueIcon('heroicon-o-check-badge')
                                        ->falseIcon('heroicon-o-x-circle')
                                        ->trueColor('success')
                                        ->falseColor('danger')
                                        ->columnSpan(2),
                                ]),
                        ]),
                ])
                    ->from('lg')
                    ->columnSpan('full'),

                // Categories and Brand Section
                InfoSection::make('Classification')
                    ->icon('heroicon-m-tag')
                    ->schema([
                        InfoGrid::make(2)
                            ->schema([
                                TextEntry::make('category.name')
                                    ->label('Category')
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-m-folder')
                                    ->placeholder('Uncategorized'),

                                TextEntry::make('brand.name')
                                    ->label('Brand')
                                    ->badge()
                                    ->color('gray')
                                    ->icon('heroicon-m-building-office')
                                    ->placeholder('No brand assigned'),
                            ]),
                    ])
                    ->columns(2),

                // Management Information Section
                InfoSection::make('Management Information')
                    ->icon('heroicon-m-users')
                    ->schema([
                        InfoGrid::make(3)
                            ->schema([
                                TextEntry::make('user.name')
                                    ->label('Created By')
                                    ->badge()
                                    ->color('primary')
                                    ->icon('heroicon-m-user')
                                    ->placeholder('System'),

                                TextEntry::make('created_at')
                                    ->label('Created At')
                                    ->dateTime('M j, Y \a\t g:i A')
                                    ->icon('heroicon-m-calendar-days')
                                    ->tooltip(fn($state) => $state->diffForHumans()),

                                TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->dateTime('M j, Y \a\t g:i A')
                                    ->icon('heroicon-m-clock')
                                    ->tooltip(fn($state) => $state->diffForHumans())
                                    ->color('warning'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
