<?php

namespace App\Exports;

use App\Models\ProductImportItem;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Carbon;

class ProductImportItemsExport implements FromQuery, WithMapping, WithHeadings
{
    public function query()
    {
        return ProductImportItem::with([
            'product',
            'productImport.supplier',
            'productImport.user',
            'productImport.items', // ⬅️ needed for total
        ]);
    }

    public function map($item): array
    {
        $import = $item->productImport;

        // calculate total price for this import (only once per item)
        $totalPrice = $import->items->sum(function ($item) {
            return $item->qty * $item->unit_price;
        });

        return [
            $import->id,
            Carbon::parse($import->import_date)->format('d/m/Y'),
            optional($import->supplier)->name,
            optional($import->user)->name,
            optional($item->product)->name,
            $item->qty,
            $item->unit_price,
            $item->qty * $item->unit_price,
            $totalPrice, // ⬅️ Add total import price here
        ];
    }

    public function headings(): array
    {
        return [

            'Import ID',
            'Import Date',
            'Supplier',
            'Imporeted By',
            'Product',
            'Quantity',
            'Unit Price',
            'Subtotal',
            'Total Price'
        ];
    }
}
