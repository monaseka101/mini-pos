<?php

namespace App\Filament\Pages;

use App\Filament\Resources\SaleResource\Widgets\Brandbar;
use App\Filament\Resources\SaleResource\Widgets\Brandpie;
use App\Filament\Resources\SaleResource\Widgets\Categorybar;
use App\Filament\Widgets\SaleActivityChart;
use Filament\Pages\Page;
use App\Filament\Resources\SaleResource\Widgets\SaleWidget;
use App\Filament\Resources\SaleResource\Widgets\Categorypie;
use App\Filament\Resources\SaleResource\Widgets\CustomerTable;
use App\Filament\Widgets\SalesChart;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class SaleReport extends Page
{
    use InteractsWithPageFilters;
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $title = 'Sale Report';
    protected static ?string $navigationLabel = 'Sale Report';
    protected static ?string $navigationGroup = 'Reports';

    protected static string $view = 'filament.sale-report';

    public function getHeaderWidgets(): array
    {
        return [

            SaleWidget::class,
            SalesChart::class,
            SaleActivityChart::class,
            Categorypie::class,
            Brandpie::class,
            Categorybar::class,
            Brandbar::class,
            CustomerTable::class,
        ];
    }
}
