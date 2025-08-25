<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Contracts\Support\Responsable;

class CustomerExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Customer::with('sales')->get(); // ✅ eager load sales
    }

    public function headings(): array
    {
        return [
            'Name',
            'Gender',
            'Phone',
            'Address',
            'Active',
            'Total Spent', // ✅ New column
        ];
    }

    public function map($customer): array
    {
        $totalSpent = $customer->sales?->sum(fn($sale) => $sale->totalPrice()) ?? 0;
        $totalSpentFormatted = '$' . number_format($totalSpent, 2);


        return [
            $customer->name,
            $customer->gender?->value ?? '',
            (string) $customer->phone,
            $customer->address,
            $customer->Active ? 'No' : 'Yes',
            $totalSpentFormatted, // ✅ Added here
        ];
    }
}
