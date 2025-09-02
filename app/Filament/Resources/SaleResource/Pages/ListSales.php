<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Concerns\ExposesTableToWidgets;

class ListSales extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
<<<<<<< HEAD

=======
>>>>>>> 8c30c670a9ec1afb31c671cb61f24a17e45bfe73
    protected function getHeaderWidgets(): array
    {
        return SaleResource::getWidgets();
    }
}
