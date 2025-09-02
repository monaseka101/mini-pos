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
use App\Filament\Resources\FontWeight;
use App\Filament\Resources\UserResource\Pages\EditUser;
use Filament\Support\Enums\FontWeight as EnumsFontWeight;
use Filament\Tables\Columns\Layout\Stack;
use Illuminate\Support\Facades\Auth;
use Filament\Facades\Filament;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-m-users';

    protected static ?string $navigationGroup = 'People';

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
            ->modifyQueryUsing(fn(Builder $query) => $query->where('id', '!=', auth()->id()))
            ->columns([
                Stack::make([
                    Tables\Columns\ImageColumn::make('avatar_url')
                        ->defaultImageUrl(fn(User $record) => User::getDefaultAvatar($record->name))
                        ->circular(),
                    Tables\Columns\TextColumn::make('name')
<<<<<<< HEAD
                        ->searchable()
                        ->weight(FontWeight::Bold)
                        ->formatStateUsing(fn($record) => $record->name . ' (' . $record->role->name . ')'),
=======
                        ->searchable(['phone_number', 'user_id'])
                        ->weight(EnumsFontWeight::Bold)
                    /* ->formatStateUsing(fn($record) => $record->name . ' (' . $record->role->name . ')') */,
>>>>>>> 8c30c670a9ec1afb31c671cb61f24a17e45bfe73
                    Tables\Columns\TextColumn::make('email')
                        ->searchable(),
                    Tables\Columns\IconColumn::make('active')
                        ->boolean(),
                ])
                    ->alignCenter()
                    ->space(2)
<<<<<<< HEAD
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Status')
                    ->placeholder('All Users')
                    ->trueLabel('Active ')
                    ->falseLabel('Inactive ')
=======


>>>>>>> 8c30c670a9ec1afb31c671cb61f24a17e45bfe73
            ])
            ->defaultSort('role', 'asc')
            ->actions([
                Tables\Actions\Action::make('activate')
                    ->button()
                    ->label('Activate')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
<<<<<<< HEAD
                    ->hidden(fn(User $record) => ! auth()->user()->isAdmin() || $record->isAdmin())
                    ->action(fn(User $record) => $record->update(['active' => true])),
                Tables\Actions\Action::make('deactivate')
                    ->button()
                    ->label('Deactivate')
                    ->hidden(fn(User $record) => ! auth()->user()->isAdmin() || $record->isAdmin())
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->action(fn(User $record) => $record->update(['active' => false])),
            ])
            // ->bulkActions([
            //     Tables\Actions\BulkAction::make('activate')
            //         ->button()
            //         ->label('Activate')
            //         ->icon('heroicon-m-check-circle')
            //         ->color('success')
            //         ->action(fn(Collection $records) => $records->each->update(['active' => true])),
            // ])
=======
                    ->hidden(fn(User $record) => !EditUser::canEdit() || $record->role === Role::Admin)
                    ->authorize(fn() => Filament::auth()->user()->role === Role::Admin)
                    ->action(fn(User $record) => $record->update(['active' => true])),

                Tables\Actions\Action::make('deactivate')
                    ->button()
                    ->label('Deactivate')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->hidden(fn(User $record) => !EditUser::canEdit() || $record->role === Role::Admin)
                    ->authorize(fn() => Filament::auth()->user()->role === Role::Admin)
                    ->action(fn(User $record) => $record->update(['active' => false])),
            ])
            ->recordUrl(function (User $record) {
                return Filament::auth()->user()->role === Role::Admin
                    ? Pages\EditUser::getUrl(['record' => $record])
                    : null;
            })
            /* ->bulkActions([
                Tables\Actions\BulkAction::make('activate')
                    ->button()
                    ->label('Activate')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->hidden(fn() => ! Edituser::canEdit())
                    ->action(fn(Collection $records) => $records->each->update(['active' => true])),
            ]) */
>>>>>>> 8c30c670a9ec1afb31c671cb61f24a17e45bfe73
            ->contentGrid(
                [
                    'md' => 2,
                    'xl' => 3,
                ]
            );
    }

<<<<<<< HEAD
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
=======
>>>>>>> 8c30c670a9ec1afb31c671cb61f24a17e45bfe73

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
            // 'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
    public static function canCreate(): bool
    {
        return Auth::user()?->role !== Role::Cashier;
    }
}
