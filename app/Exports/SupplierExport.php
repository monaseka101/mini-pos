<?php

namespace App\Exports;

use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class SupplierExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Supplier::select('id', 'name', 'phone', 'bank_name', 'account_number', 'address', 'active')->get();
    }

    public function headings(): array
    {
        return [
            'Supplier ID',
            'Name',
            'Phone',
            'Bank Name',
            'Account Number',
            'Address',
            'Active',
        ];
    }

    public function map($supplier): array
    {
        return [
            $supplier->id,
            $supplier->name,
            (string)$supplier->phone,  // cast phone to string
            $supplier->bank_name,
            $supplier->account_number,
            $supplier->address,
            $supplier->active ? 'Yes' : 'No',
        ];
    }
}
