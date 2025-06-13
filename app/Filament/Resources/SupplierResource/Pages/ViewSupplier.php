<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;

class ViewSupplier extends ViewRecord
{
    protected static string $resource = SupplierResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Supplier Information')
                    ->description('Basic supplier details')
                    ->icon('heroicon-m-building-office-2')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('name')
                                ->label('Supplier Name')
                                ->icon('heroicon-m-user')
                                ->weight(FontWeight::Medium),

                            TextEntry::make('phone')
                                ->label('Phone Number')
                                ->icon('heroicon-m-phone')
                                ->url(fn($record) => $record->phone ? 'tel:' . $record->phone : null)
                                ->color('primary'),

                            TextEntry::make('address')
                                ->label('Address')
                                ->icon('heroicon-m-map-pin')
                                ->columnSpanFull(),
                        ]),
                    ]),

                Section::make('Banking Information')
                    ->description('Financial and payment details')
                    ->icon('heroicon-m-banknotes')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('bank_name')
                                ->label('Bank Name')
                                ->icon('heroicon-m-building-library')
                                ->badge()
                                ->color('info'),

                            TextEntry::make('account_number')
                                ->label('Account Number')
                                ->icon('heroicon-m-credit-card')
                                ->badge()
                                ->color('gray')
                                ->formatStateUsing(
                                    fn(string $state): string =>
                                    implode(' ', str_split($state, 4))
                                ),
                        ]),
                    ]),

                Section::make('Status & Timestamps')
                    ->description('Supplier status and record information')
                    ->icon('heroicon-m-clock')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('active')
                                ->label('Status')
                                ->badge()
                                ->formatStateUsing(fn(bool $state): string => $state ? 'Active' : 'Inactive')
                                ->color(fn(bool $state): string => $state ? 'success' : 'danger')
                                ->icon(fn(bool $state): string => $state ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle'),

                            TextEntry::make('created_at')
                                ->label('Created')
                                ->icon('heroicon-m-calendar-days')
                                ->dateTime()
                                ->color('gray'),

                            TextEntry::make('updated_at')
                                ->label('Last Updated')
                                ->icon('heroicon-m-arrow-path')
                                ->dateTime()
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
