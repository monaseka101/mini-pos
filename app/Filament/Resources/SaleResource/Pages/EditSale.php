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

    protected function beforeSave()
    {
        Log::info($this->record);
    }

    protected function afterSave()
    {
        $this->record->load('items.discount');

        $this->record->total_pay = $this->record->totalPay();
        $this->record->save(); // save updated total_pay

        //Log::info('Updated total_pay:', ['total_pay' => $this->record->total_pay]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
