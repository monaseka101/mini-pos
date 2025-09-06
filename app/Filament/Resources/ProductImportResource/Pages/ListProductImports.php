<?php

namespace App\Filament\Resources\ProductImportResource\Pages;

use App\Filament\Resources\ProductImportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductImports extends ListRecords
{
    protected static string $resource = ProductImportResource::class;
    protected static ?string $title = 'Product Import History';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create Import')
                ->url('import-page'),
            // Actions\Action::make('sd')
            //     ->color('danger')
            //     ->url(SaleResource::getUrl('create'))
            //     ->label('Dumb Sale'),
        ];
    }
}
