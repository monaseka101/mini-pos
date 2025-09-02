<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Product::with(['brand', 'category'])->get()->map(function ($product) {
            return [
                'name'        => $product->name,
                'price'       => $product->price,
                'stock'       => (string) $product->stock,
                'brand'       => $product->brand?->name,
                'category'    => $product->category?->name,
                'description' => $product->description,
                'active'      => $product->active,
                'created_at'  => $product->created_at?->format('d/m/Y H:i:s'),
                'updated_at'  => $product->updated_at?->format('d/m/Y H:i:s'),
            ];
        });
    }
    public function headings(): array
    {
        return [
            'Name',
            'Price',
            'Stock',
            'Brand',
            'Category',
            'Description',
            'Active',
            'Created At',
            'Updated At',
        ];
    }
}
