<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Helpers\Util;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Table;
use Filament\Tables\Filters;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\FontWeight;
use Closure;

class CustomListProducts extends ListRecords
{
    use ExposesTableToWidgets;

    protected static ?string $title = 'Choose Products to Buy';

    protected static string $resource = ProductResource::class;


    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Stack::make([
                    Tables\Columns\ImageColumn::make('image')
                        ->height(100)
                        ->defaultImageUrl(fn($record) => Util::getDefaultAvatar($record->name)),
                    Tables\Columns\TextColumn::make('name')
                        ->weight(FontWeight::Bold)
                        ->size('lg'),
                    Tables\Columns\TextColumn::make('price')
                        ->state(fn($record) => '$' . number_format($record->price, 2))
                        ->weight(FontWeight::Bold)
                        ->color('success')
                        ->size('lg'),

                    // Tables\Columns\TextColumn::make('description')
                    //     ->html()
                    //     ->words(15)
                    //     ->wrap()
                ])
                    ->space(3)
            ])
            ->actions([
                Tables\Actions\Action::make('select')
                    ->label('Add to Cart')
                    ->color('primary')
                    ->icon('heroicon-o-shopping-cart')
                    ->url(fn($record) => $record) // TODO: Fix this
                    ->button()
            ])
            ->recordUrl(null)
            ->contentGrid([
                'sm' => 1,
                'md' => 2,
                'lg' => 3,
                'xl' => 4,
            ])
            ->defaultPaginationPageOption(12);
    }
}
