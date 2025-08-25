<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;

class ViewSale extends ViewRecord
{
    protected static string $resource = SaleResource::class;

    /**
     * Configure the Infolist layout and fields shown on the page
     */
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Section: General sale information
                Section::make('Sale Information')
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
                                    ->weight(FontWeight::SemiBold),

                                TextEntry::make('customer.phone')
                                    ->label('Phone')
                                    ->icon('heroicon-o-phone')
                                    ->copyable()
                                    ->copyMessage('Phone number copied!')
                                    ->placeholder('No phone number'),

                                TextEntry::make('user.name')
                                    ->label('Created by')
                                    ->icon('heroicon-o-user-circle')
                                    ->badge()
                                    ->color('success'),
                            ]),
                    ])
                    ->columns(1),

                // Section: Sale items details with repeatable entries for each item
                Section::make('Sale Items')
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

                                            // Discount display, formatted as percent or fixed amount
                                            TextEntry::make('discount.value')
                                                ->label('Discount')
                                                ->formatStateUsing(function ($state, $record) {
                                                    if ($record->discount?->ispercent == '1') {
                                                        return $state . '%';
                                                    }
                                                    return '-$' . number_format($state, 2);
                                                }),

                                            // Total price after discount per item
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

                        // Summary grid (you have placeholders 'd', 's', 'x'—consider removing or repurposing)
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('d')->label(''), // Placeholder, can be removed
                                TextEntry::make('s')->label(''), // Placeholder, can be removed
                                TextEntry::make('x')->label(''), // Placeholder, can be removed

                                // Subtotal for all items after discount
                                TextEntry::make('total_amount')
                                    ->label('Sub Total')
                                    ->state(function ($record) {
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

                // Section: Additional notes for the sale
                Section::make('Additional Information')
                    ->schema([
                        TextEntry::make('note')
                            ->label('Notes')
                            ->html()
                            ->placeholder('No additional notes')
                            ->columnSpanFull(),
                    ])
                    ->collapsed()
                    ->collapsible(),
            ]);
    }

    /**
     * Actions available in the page header (e.g., edit button)
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
