<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ProductResource\Widgets\Brandbarcount;
use App\Filament\Resources\ProductResource\Widgets\Categorybarcount;
use App\Filament\Resources\ProductResource\Widgets\Producthistory;
use App\Filament\Resources\ProductResource\Widgets\Productline;
use App\Filament\Resources\ProductResource\Widgets\ProductStats;
use Filament\Pages\Page;

class ProductReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $title = 'Product Report';
    protected static ?string $navigationLabel = 'Product Report';
    protected static ?string $navigationGroup = 'Reports';
    protected static string $view = 'filament.product-report';

    protected function getHeaderWidgets(): array
    {
        return [
            ProductStats::class,
            Brandbarcount::class,
            Categorybarcount::class,
            Productline::class,
            Producthistory::class,
        ];
    }
}
