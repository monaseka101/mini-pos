<?php

namespace App\Exports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CategoriesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Category::select('id', 'name', 'description', 'active', 'created_at', 'updated_at')->get();
    }

    public function headings(): array
    {
        return [
            'Category_ID',
            'Name',
            'Description',
            'Active',
            'Created At',
            'Updated At',
        ];
    }
}
