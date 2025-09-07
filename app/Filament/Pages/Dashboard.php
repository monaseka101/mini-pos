<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ProductImportResource\Widgets\ExpenseStats;
use App\Filament\Resources\ProductImportResource\Widgets\ProductImportChart;
use App\Filament\Resources\ProductResource\Widgets\LowStockProduct;
use App\Filament\Resources\SaleResource\Widgets\SaleActivityChart;
use App\Filament\Resources\SaleResource\Widgets\SaleBrandChart;
use App\Filament\Resources\SaleResource\Widgets\SaleChart;
use App\Filament\Resources\SaleResource\Widgets\SaleStats;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Placeholder;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    use BaseDashboard\Concerns\HasFiltersForm;

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Placeholder::make('new_sale_btn')
                    ->label('')
                    ->content(new HtmlString(
                        '<a href="' . ShopPage::getUrl() . '" 
                        class="mt-6 w-full h-10 flex items-center justify-center rounded-lg bg-primary-600 text-white text-sm font-medium shadow hover:bg-primary-700 transition">
                        Click to do Sale
                    </a>'
                    )),
                /* Placeholder::make('new_import_btn')
                    ->label('')

                    ->content(new HtmlString(
                        '<a href="' . ImportPage::getUrl() . '" 
                        class="mt-6 w-full h-10 flex items-center justify-center rounded-lg bg-primary-600 text-white text-sm font-medium shadow hover:bg-primary-700 transition">
                        Click to Import Stock
                    </a>'
                    )), */
            ])
            ->columns(2);
    }
    /* protected function getHeaderActions(): array
    {
        return [
            Action::make('New Sale')
                ->label('New Sale')
                ->url(\App\Filament\Resources\SaleResource::getUrl('create'))
                ->color('primary')
                ->size('xl'),

            Action::make('New Import')
                ->label('New Import')
                ->url(\App\Filament\Resources\CustomerResource::getUrl('create'))
                ->color('primary')
                ->size('xl'),
        ];
    } */

    public function getWidgets(): array
    {
        return [
            SaleStats::class,
            ExpenseStats::class,
            // SaleStats::class,
            LowStockProduct::class,
            // SaleActivityChart::class,
            // SaleChart::class,
            // ProductImportChart::class,
            // SaleBrandChart::class,
        ];
    }
}
