<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;

class EditSale extends EditRecord
{
    protected static string $resource = SaleResource::class;

    /**
     * Hook before saving the record
     */
    protected function beforeSave(): void
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
