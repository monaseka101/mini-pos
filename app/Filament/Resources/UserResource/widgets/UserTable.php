<?php

namespace App\Filament\Resources\UserResource\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UserTable extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 3;
    protected static ?string $heading = 'Total Income by User';

    // Define the table configuration
    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    // Eager load sum of related sales 'total_pay' as total_income
                    ->withSum('sales as total_income', 'total_pay')
                    // Subquery to calculate total quantity sold by user (sum qty in sale_items linked by sales)
                    ->selectSub(function ($query) {
                        $query->from('sale_items')
                            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                            ->selectRaw('SUM(sale_items.qty)')
                            ->whereColumn('sales.user_id', 'users.id');
                    }, 'total_qty_sold')
            )
            // Pagination: default to 5 rows per page
            ->defaultPaginationPageOption(4)
            // Default sorting: newest users first by created_at descending
            ->defaultSort('created_at', 'desc')
            // Define columns
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('User name')
                    ->searchable() // allows searching/filtering by name
                    ->formatStateUsing(fn($record) => $record->name),

                Tables\Columns\TextColumn::make('role')
                    ->label('Role')
                    // Use custom enum label if available, otherwise 'N/A'
                    ->formatStateUsing(fn($state, $record) => $record->getRoleEnum()?->getLabel() ?? 'N/A')
                    // Color column based on role enum color
                    ->color(fn($state, $record) => $record->getRoleEnum()?->getColor()),

                Tables\Columns\TextColumn::make('total_qty_sold')
                    ->label('Qty Sold')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_income')
                    ->label('Income Gain')
                    ->money(currency: 'usd') // display currency format
                    ->sortable()
                    ->badge() // show as badge style
                    ->formatStateUsing(fn($state) => '$' . number_format($state ?? 0, 2)),
            ]);
    }
}
