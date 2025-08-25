<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    // public function getTabs(): array
    // {
    //     return [
    //         'all' => Tab::make('All users'),
    //         'active' => Tab::make('Active users')
    //             ->modifyQueryUsing(fn (Builder $query) => $query->where('active', true)),
    //         'inactive' => Tab::make('Inactive users')
    //             ->modifyQueryUsing(fn (Builder $query) => $query->where('active', false)),
    //     ];
    // }
}
