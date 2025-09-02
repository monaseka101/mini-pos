<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Enums\Role;
use App\Filament\Resources\SupplierResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditSupplier extends EditRecord
{
    protected static string $resource = SupplierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
        ];
    }
    public static function CanEdit(): bool
    {
        return Auth::user()?->role !== Role::Cashier;
    }
}
