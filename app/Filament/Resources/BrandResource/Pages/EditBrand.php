<?php

namespace App\Filament\Resources\BrandResource\Pages;

use App\Enums\Role;
use App\Filament\Resources\BrandResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditBrand extends EditRecord
{
    protected static string $resource = BrandResource::class;
    public static function CanEdit(): bool
    {
        return Auth::user()?->role !== Role::Cashier;
    }
}
