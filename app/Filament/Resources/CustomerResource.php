<?php

namespace App\Filament\Resources;

use App\Enums\Gender;
use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Filament\Resources\CustomerResource\RelationManagers\SalesRelationManager;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-m-user-group';

    protected static ?string $navigationGroup = 'Customer & Supplier';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->prefixIcon('heroicon-m-user')
                    ->maxLength(255)
                    ->required(),
                Forms\Components\Select::make('gender')
                    ->enum(Gender::class)
                    ->prefixIcon('heroicon-m-user-circle')
                    ->options(Gender::class),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(20)
                    ->prefixIcon('heroicon-m-phone')
                    ->placeholder('096-234-2345'),
                Forms\Components\DatePicker::make('date_of_birth')
                    ->date()
                    ->label('Date of Birth')
                    ->prefixIcon('heroicon-m-calendar-days'),
                Forms\Components\TextInput::make('address')
                    ->label('Street Address')
                    ->maxLength(255)
                    ->prefixIcon('heroicon-m-map-pin')
                    ->placeholder('123 Main Street')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('active')
                    ->default(true)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('gender')
                    ->badge(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),

                Tables\Columns\TextColumn::make('address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/y h:m:s')
                    ->dateTooltip('d/M/Y h:m:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Date of Birth')
                    ->date('d/m/Y')
                    ->dateTooltip('d/M/Y')
                    ->sortable(),
                Tables\Columns\IconColumn::make('active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('gender')
                    ->options(Gender::class),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
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
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Personal Information')
                    ->description('Customer basic details')
                    ->icon('heroicon-m-identification')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('name')
                                ->label('Customer Name')
                                ->icon('heroicon-m-user')
                                ->weight(FontWeight::Bold)
                                ->size(TextEntry\TextEntrySize::Large),

                            TextEntry::make('gender')
                                ->label('Gender')
                                ->icon('heroicon-m-user-circle')
                                ->badge()
                                ->color('info'),

                            TextEntry::make('date_of_birth')
                                ->label('Birth Date')
                                ->icon('heroicon-m-calendar')
                                ->date('F j, Y')
                                ->badge()
                                ->color('gray'),
                        ]),
                        Grid::make(4)
                        ->schema([
                            // TextEntry::make('total_items')
                            //     ->label('Total Items')
                            //     ->state(function ($record) {
                            //         return $record->items->sum('qty');
                            //     })
                            //     ->badge()
                            //     ->color('info')
                            //     ->icon('heroicon-o-list-bullet'),
                            TextEntry::make('d')
                                ->label(''),
                            TextEntry::make('s')
                                ->label(''),
                            TextEntry::make('x')
                                ->label(''),
                        ])
                    ]),

                Section::make('Contact Details')
                    ->description('How to reach the customer')
                    ->icon('heroicon-m-phone')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('phone')
                                ->label('Phone Number')
                                ->icon('heroicon-m-device-phone-mobile')
                                ->copyable()
                                ->copyMessage('Phone number copied!')
                                ->copyMessageDuration(1500)
                                ->url(fn($record) => $record->phone ? 'tel:' . $record->phone : null)
                                ->color('primary'),

                            TextEntry::make('address')
                                ->label('Address')
                                ->icon('heroicon-m-map-pin')
                                ->copyable()
                                ->copyMessage('Address copied!')
                                ->copyMessageDuration(1500)
                                ->columnSpanFull(),
                        ]),
                    ]),

                Section::make('Account Status')
                    ->description('Customer account information')
                    ->icon('heroicon-m-shield-check')
                    ->collapsed()
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('active')
                                ->label('Status')
                                ->badge()
                                ->formatStateUsing(fn(bool $state): string => $state ? 'Active Customer' : 'Inactive Customer')
                                ->color(fn(bool $state): string => $state ? 'success' : 'danger')
                                ->icon(fn(bool $state): string => $state ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle'),

                            TextEntry::make('created_at')
                                ->label('Customer Since')
                                ->icon('heroicon-m-calendar-days')
                                ->date('M d, Y')
                                ->badge()
                                ->color('gray'),

                            TextEntry::make('updated_at')
                                ->label('Last Updated')
                                ->icon('heroicon-m-clock')
                                ->dateTime('M d, Y H:i')
                                ->since()
                                ->badge()
                                ->color('gray'),
                        ]),
                    ]),

                Section::make('Customer Statistics')
                    ->description('Overview of customer activity')
                    ->icon('heroicon-m-chart-bar')
                    ->collapsed()
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('sales_count')
                                ->label('Total Sales')
                                ->icon('heroicon-m-shopping-cart')
                                ->state(function ($record) {
                                    return $record->sales()->count();
                                })
                                ->badge()
                                ->color('success'),

                            TextEntry::make('total_spent')
                                ->label('Total Spent')
                                ->icon('heroicon-m-banknotes')
                                ->state(function ($record) {
                                    $total = 0;

                                    foreach ($record->sales as $sale) {
                                        $total += $sale->totalPrice();
                                    }

                                    return $total;
                                })
                                ->money('USD')
                                ->badge()
                                ->color('success'),

                            TextEntry::make('last_sale')
                                ->label('Last Sale')
                                ->icon('heroicon-m-calendar')
                                ->state(function ($record) {
                                    $lastSale = $record->sales()->latest()->first();
                                    return $lastSale ? $lastSale->created_at : 'No sales yet';
                                })
                                ->formatStateUsing(function ($state) {
                                    return $state instanceof \DateTime ? $state->format('M d, Y') : $state;
                                })
                                ->badge()
                                ->color('gray'),
                        ]),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            SalesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'customer.view' => Pages\ViewCustomer::route('/{record}'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
