<?php

namespace App\Exports;

use App\Models\SaleItem;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SalesItemExport implements FromQuery, WithMapping, WithHeadings
{
    public function query()
    {
        return SaleItem::with([
            'sale.customer',
            'sale.user',
            'product',
            'sale.items', // required to calculate total sale amount
        ]);
    }

    public function map($item): array
    {
        $sale = $item->sale;

        // Calculate total sale amount (from all sale items)
        $totalPrice = $sale->items->sum(function ($item) {
            return $item->qty * $item->unit_price;
        });

        return [
            $sale->id,
            Carbon::parse($sale->sale_date)->format('d/m/Y'),
            optional($sale->customer)->name,
            optional($sale->user)->name,
            optional($item->product)->name,
            $item->qty,
            $item->unit_price,
            $item->qty * $item->unit_price,
            $totalPrice,
        ];
    }

    public function headings(): array
    {
        return [
            'Sale ID',
            'Sale Date',
            'Customer',
            'Sold By',
            'Product',
            'Quantity',
            'Unit Price',
            'Subtotal',
            'Total Sale Amount',
        ];
    }
}
