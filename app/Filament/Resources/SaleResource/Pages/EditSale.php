<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;

class EditSale extends EditRecord
{
    protected static string $resource = SaleResource::class;

<<<<<<< HEAD
    protected function mutateFormDataBeforeFill(array $data): array
    {
        Log::info('Sale Items:', [
            'sale_id' => $this->record->id,
            'items' => $this->record->items()->get()
        ]);

        return $data;
    }


    protected function beforeSave()
=======
    /**
     * Hook before saving the record
     */
    protected function beforeSave(): void
>>>>>>> 8c30c670a9ec1afb31c671cb61f24a17e45bfe73
    {
        // Optional debug log of current record before save
        Log::info('Before save Sale record:', ['record' => $this->record]);
    }

    /**
     * After saving:
     * - Recalculate total_pay
     * - Reload related items and discounts
     * - Save updated total
     */
    protected function afterSave(): void
    {
        // Ensure all related data is loaded
        $this->record->load('items.discount');

        // Recalculate and update total
        $this->record->total_pay = $this->record->totalPay();
        $this->record->save();

        // Log optional debug info
        // Log::info('Updated total_pay:', ['total_pay' => $this->record->total_pay]);
    }

    /**
     * Define available actions in the header (e.g., Delete button)
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
