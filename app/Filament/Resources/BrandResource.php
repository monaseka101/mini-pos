<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\Pages;
use App\Filament\Resources\BrandResource\RelationManagers;
use App\Filament\Resources\BrandResource\RelationManagers\ProductsRelationManager;
use App\Helpers\Util;
use App\Models\Brand;
use App\Models\User;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Exports\BrandExporter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;
use Parfaitementweb\FilamentCountryField\Forms\Components\Country;
use Parfaitementweb\FilamentCountryField\Tables\Columns\CountryColumn;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static ?string $navigationIcon = 'heroicon-o-bookmark-square';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->maxLength(255)
                    ->required(),
                Forms\Components\TextInput::make('made_in')
                    ->label('Made In'),
                Forms\Components\TextInput::make('website')
                    ->label('Official website')
                    ->unique(ignoreRecord: true)
                    ->url()
                    ->maxLength(255)
                    ->placeholder('https://brand.com')
                    ->prefixIcon('heroicon-m-link'),
                Forms\Components\FileUpload::make('logo')
                    ->label('Logo')
                    ->image()
                    ->imageEditor()
                    ->imageEditorAspectRatios(['1:1'])
                    ->imageResizeMode('cover')
                    ->imageCropAspectRatio('1:1')
                    ->imageResizeTargetWidth('300')
                    ->imageResizeTargetHeight('300')
                    ->directory('logos')
                    ->visibility('public')
                    ->maxSize(2048)
                    ->helperText('Upload a logo picture (max 2MB)')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('active')
                    ->default(state: true)
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->defaultImageUrl(fn(Brand $record) => Util::getDefaultAvatar($record->name)),
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('website')
                    ->searchable()
                    ->url(fn($state) => $state)
                    ->icon('heroicon-m-link')
                    ->tooltip('Click to open website')
                    ->copyable()
                    ->copyMessage('Website URL copied!')
                    ->copyMessageDuration(1500)
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('made_in')
                    ->badge()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\IconColumn::make('active')
                    ->boolean(),
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
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Status')
                    ->placeholder('All Brands')
                    ->trueLabel('Active Brand')
                    ->falseLabel('Inactive Brand'),

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                ExportBulkAction::make()
                    ->label('Export Selected Categories')
                    ->color('primary')
                    ->exporter(BrandExporter::class)

            ])
            ->defaultSort('active', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\Section::make('Brand Information')
                    ->description('Basic brand details and identity')
                    ->icon('heroicon-m-bookmark-square')
                    ->schema([
                        \Filament\Infolists\Components\Grid::make(2)
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('name')
                                    ->label('Brand Name')
                                    ->icon('heroicon-m-tag')
                                    ->weight(\Filament\Support\Enums\FontWeight::Bold)
                                    ->size(\Filament\Infolists\Components\TextEntry\TextEntrySize::Large),

                                \Filament\Infolists\Components\TextEntry::make('made_in')
                                    ->label('Made In')
                                    ->icon('heroicon-m-globe-americas')
                                    ->badge()
                                    ->color('info'),
                            ]),

                        \Filament\Infolists\Components\TextEntry::make('website')
                            ->label('Official Website')
                            ->icon('heroicon-m-link')
                            ->url(fn($state) => $state)
                            ->openUrlInNewTab()
                            ->copyable()
                            ->copyMessage('Website URL copied!')
                            ->copyMessageDuration(1500),
                    ]),

                \Filament\Infolists\Components\Section::make('Brand Media')
                    ->description('Brand logo and visual identity')
                    ->icon('heroicon-m-photo')
                    ->schema([
                        \Filament\Infolists\Components\ImageEntry::make('logo')
                            ->label('Brand Logo')
                            ->defaultImageUrl(fn($record) => Util::getDefaultAvatar($record->name))
                            ->size(200)
                    ]),

                \Filament\Infolists\Components\Section::make('Status & Statistics')
                    ->description('Brand activity information')
                    ->icon('heroicon-m-chart-bar')
                    ->schema([
                        \Filament\Infolists\Components\Grid::make(3)
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('active')
                                    ->label('Status')
                                    ->badge()
                                    ->formatStateUsing(fn(bool $state): string => $state ? 'Active Brand' : 'Inactive Brand')
                                    ->color(fn(bool $state): string => $state ? 'success' : 'danger')
                                    ->icon(fn(bool $state): string => $state ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle'),

                                \Filament\Infolists\Components\TextEntry::make('products_count')
                                    ->label('Total Products')
                                    ->state(fn($record) => $record->products()->count())
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-m-squares-2x2'),

                                \Filament\Infolists\Components\TextEntry::make('created_at')
                                    ->label('Create At')
                                    ->dateTime('d/M/Y')
                                    ->badge()
                                    ->color('gray')
                                    ->icon('heroicon-m-calendar'),
                            ]),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ProductsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'edit' => Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}
