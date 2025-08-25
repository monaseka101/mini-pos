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
    public static function CanEdit(): bool
    {
        return Auth::user()?->role !== Role::Cashier;
    }
}
