<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use App\Filament\Resources\SupplierResource;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;

class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    public function infolist(Infolist $infolist): Infolist
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



    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
