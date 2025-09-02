<?php

namespace App\Filament\Resources\ProductImportResource\Pages;

use App\Enums\Role;
use App\Filament\Resources\ProductImportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditProductImport extends EditRecord
{
    protected static string $resource = ProductImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
<<<<<<< HEAD

    protected function mutateFormDataBeforeFill(array $data): array
    {
        logger('Current Items', [$this->record->items()->get()]);
        return $data;
    }

    protected function afterSave()
    {
        logger('Edit Product Import', [$this->record['items']]);
=======
    public static function CanEdit(): bool
    {
        return Auth::user()?->role !== Role::Cashier;
>>>>>>> 8c30c670a9ec1afb31c671cb61f24a17e45bfe73
    }
}
