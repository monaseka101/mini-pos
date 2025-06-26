<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class DashboardStats extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
    protected static string $view = 'filament.pages.dashboard-stats';
    protected static ?string $title = 'Dashboard Stats';
    protected static ?string $navigationLabel = 'Dashboard Stats';
}
