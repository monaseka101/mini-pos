<?php

namespace App\Filament\Resources\SaleResource\Widgets;

use App\Models\Customer;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class CustomerTable extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 3;
    protected static ?string $heading = 'Total Amount Paid by Customer';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Customer::query()
                    // Eager load sum of related sales total_pay as total_paid
                    ->withSum('sales as total_paid', 'total_pay')
                    // Optional: subquery to get total quantity purchased by customer
                    ->selectSub(function ($query) {
                        $query->from('sale_items')
                            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                            ->selectRaw('SUM(sale_items.qty)')
                            ->whereColumn('sales.customer_id', 'customers.id');
                    }, 'total_qty_purchased')
            )
            ->defaultPaginationPageOption(5)
            ->defaultSort('total_paid', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Customer Name')
                    ->searchable()
                    ->formatStateUsing(fn($record) => $record->name),

                Tables\Columns\TextColumn::make('total_qty_purchased')
                    ->label('Qty Purchased')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_paid')
                    ->label('Total Paid')
                    ->money(currency: 'usd')
                    ->sortable()
                    ->badge()
                    ->formatStateUsing(fn($state) => '$' . number_format($state ?? 0, 2)),
            ]);
    }
}
