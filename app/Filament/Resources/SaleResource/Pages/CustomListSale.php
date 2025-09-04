<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Filament\Resources\SaleResource;
use Filament\Actions;
use Filament\Tables\Columns\TextColumn;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\Layout\Stack;
use Illuminate\Support\Facades\Log;

class CustomListSale extends ListRecords
{
    protected static string $resource = SaleResource::class;

    public function mount(): void
    {
        Log::info('CustomListSale mounted');
        parent::mount();
    }

    public function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table->columns([
            Stack::make([
                TextColumn::make('sale_date')
                    ->searchable()

            ])
                ->alignCenter()
                ->space(2)
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Custom Action'),

        ];
    }

    protected function getHeaderWidgets(): array
    {
        return SaleResource::getWidgets();
    }
}
