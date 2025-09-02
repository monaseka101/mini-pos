<?php

namespace App\Filament\Pages;

use App\Filament\Resources\UserResource\Widgets\UserTable;
use App\Filament\Resources\UserResource\Widgets\UserWidget;
use Filament\Pages\Page;

class UserReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $title = 'User Report';
    protected static ?string $navigationLabel = 'User Report';
    protected static ?string $navigationGroup = 'Reports';

    // Change this to your actual view path
    protected static string $view = 'filament.user-report';

    public function getHeaderWidgets(): array
    {
        return [
            UserWidget::class,
            UserTable::class,
        ];
    }
}
