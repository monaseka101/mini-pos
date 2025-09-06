<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Filament\Resources\SaleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Log;

class ListSales extends ListRecords
{
    protected static string $resource = SaleResource::class;
    protected static ?string $title = 'Sales History';
    // protected static string $heading = 'Sale History';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create Sale')
                ->url('shop-page'),
            // Actions\Action::make('sd')
            //     ->color('danger')
            //     ->url(SaleResource::getUrl('create'))
            //     ->label('Dumb Sale'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return SaleResource::getWidgets();
    }
}
