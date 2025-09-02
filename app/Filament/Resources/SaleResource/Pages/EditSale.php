<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EditSale extends EditRecord
{
    protected static string $resource = SaleResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        Log::info('Sale Items:', [
            'sale_id' => $this->record->id,
            'items' => $this->record->items()->get()
        ]);

        return $data;
    }


    protected function beforeSave()
    {
        Log::info($this->record);
    }

    protected function afterSave()
    {
        Log::info($this->record);
        Log::info($this->record->items);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
