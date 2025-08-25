<?php

namespace App\Filament\Resources\DiscountResource\Pages;

use App\Enums\Role;
use App\Filament\Resources\DiscountResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditDiscount extends EditRecord
{
    protected static string $resource = DiscountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }
    public static function CanEdit(): bool
    {
        return Auth::user()?->role !== Role::Cashier;
    }
}
