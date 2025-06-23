<?php

namespace App\Filament\Resources;

use App\Enums\Role;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Helpers\Util;
use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Table;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-m-users';

    protected static ?string $navigationGroup = 'User Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->unique()
                    ->required(),
                Forms\Components\Select::make('role')
                    ->options(Role::class),

                Forms\Components\TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('password_confirmation')
                    ->password()
                    ->required()
                    ->maxLength(255)
                    ->dehydrated(false)
                    ->revealable()
                    ->same('password')
                    ->label('Confirm Password'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Stack::make([
                    Tables\Columns\ImageColumn::make('avatar_url')
                        ->defaultImageUrl(fn(User $record) => User::getDefaultAvatar($record->name))
                        ->circular(),
                    Tables\Columns\TextColumn::make('name')
                        ->searchable()
                        ->weight(FontWeight::Bold)
                        ->formatStateUsing(fn($record) => $record->name . ' (' . $record->role->name . ')'),
                    Tables\Columns\TextColumn::make('email'),
                    Tables\Columns\IconColumn::make('active')
                        ->boolean(),
                ])
                    ->alignCenter()
                    ->space(2)
            ])
            ->actions([
                Tables\Actions\Action::make('activate')
                    ->button()
                    ->label('Activate')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->action(fn(User $record) => $record->update(['active' => true])),
                Tables\Actions\Action::make('deactivate')
                    ->button()
                    ->label('Deactivate')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->action(fn(User $record) => $record->update(['active' => false])),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('activate')
                    ->button()
                    ->label('Activate')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->action(fn(Collection $records) => $records->each->update(['active' => true])),
            ])
            ->contentGrid(
                [
                    'md' => 2,
                    'xl' => 3,
                ]
            );
    }

    // public static function table(Table $table): Table
    // {
    //     return $table
    //         ->columns([
    //             Split::make([
    //                 Stack::make([
    //                     Tables\Columns\ImageColumn::make('avatar_url')
    //                         ->defaultImageUrl(fn(User $record) => User::getDefaultAvatar($record->name))
    //                         ->label('Avatar')
    //                         ->circular(),
    //                     Tables\Columns\TextColumn::make('name')
    //                         ->weight(FontWeight::Bold)
    //                         ->sortable()
    //                         ->searchable(),
    //                 ])->alignment(Alignment::Center)
    //                     ->space(2),

    //                 Tables\Columns\IconColumn::make('active')
    //                     ->boolean(),
    //                 Tables\Columns\TextColumn::make('created_at')
    //                     ->dateTime('d/m/y h:m:s')
    //                     ->sortable()
    //                     ->toggleable(isToggledHiddenByDefault: true),
    //                 Tables\Columns\TextColumn::make('updated_at')
    //                     ->dateTime()
    //                     ->sortable()
    //                     ->toggleable(isToggledHiddenByDefault: true),
    //             ])
    //                 ->visibleFrom('md'),
    //             Panel::make([
    //                 Tables\Columns\TextColumn::make('email')
    //                     ->searchable(),
    //                 Tables\Columns\TextColumn::make('role')
    //                     ->badge()
    //                     ->color(function ($state) {
    //                         return $state->getColor();
    //                     }),
    //             ])->collapsible(false)
    //         ])
    //         ->filters([
    //             Tables\Filters\SelectFilter::make('role')
    //                 ->options(Role::class),
    //             Tables\Filters\TernaryFilter::make('active')
    //                 ->label('Status')
    //                 ->placeholder('All Users')
    //                 ->trueLabel('Active Users')
    //                 ->falseLabel('Inactive Users'),
    //         ])
    //         ->actions([
    //             Tables\Actions\Action::make('edit')
    //                 ->url(fn($record) => UserResource::getUrl('edit', ['record' => $record]))
    //                 ->icon('heroicon-m-pencil-square')
    //         ])
    //         ->bulkActions([
    //             Tables\Actions\BulkAction::make('activate')
    //                 ->requiresConfirmation()
    //                 ->label('Activate Selected')
    //                 ->icon('heroicon-m-check-circle')
    //                 ->color('success')
    //                 ->action(fn(Collection $records) => $records->each->update(['active' => true])),
    //             Tables\Actions\BulkAction::make('deactivate')
    //                 ->requiresConfirmation()
    //                 ->label('Deactivate Selected')
    //                 ->icon('heroicon-m-x-circle')
    //                 ->color('danger')
    //                 ->action(fn(Collection $records) => $records->each->update(['active' => false])),
    //         ])
    //         ->defaultSort('active', 'desc');
    // }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
