<?php

namespace App\Filament\Pages;


use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('startDate')
                    ->label('Start date')
                    ->default(fn() => now()->year >= 2023 ? \Carbon\Carbon::create(2023, 1, 1) : now())
                    ->maxDate(fn(Get $get) => $get('endDate') ?: now()),

                DatePicker::make('endDate')
                    ->label('End date')
                    ->minDate(fn(Get $get) => $get('startDate') ?: now())
                    ->maxDate(now()),
            ])
            ->columns(2);
    }

    /**
     * Big buttons in the top-right corner
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('New Sale')
                ->label('New Sale')
                ->url(\App\Filament\Resources\SaleResource::getUrl('create'))
                ->color('primary')
                ->size('xl'),

            Action::make('New Customer')
                ->label('New Customer')
                ->url(\App\Filament\Resources\CustomerResource::getUrl('create'))
                ->color('primary')
                ->size('xl'),
        ];
    }
}
