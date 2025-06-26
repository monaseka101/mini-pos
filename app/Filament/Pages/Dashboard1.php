<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Dashboard1 extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $title = 'Sale Report';
    protected static ?string $navigationLabel = 'Sale Report';
    protected static ?string $navigationGroup = 'Reports';

    // Use the default Filament page view
    protected static string $view = 'filament::page';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\SaleStats::class,
            \App\Filament\Widgets\InventoryStats::class,
            // add other widgets here
        ];
    }
}
