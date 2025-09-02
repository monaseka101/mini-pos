<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Enums\Role;
use App\Filament\Resources\UserResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('role')
                    ->options(Role::class)
                    ->columnSpanFull()
                    ->hidden(fn() => Filament::auth()->user()?->role === Role::Admin),


                Forms\Components\TextInput::make('password')
                    ->label('New Password')
                    ->placeholder('Leave blank to keep current password')
                    ->password()
                    ->revealable()
                    ->maxLength(255)
                    // if the field is filled means password want to update
                    ->dehydrated(fn($state) => filled($state)),

                Forms\Components\TextInput::make('password_confirmation')
                    ->password()
                    ->dehydrated(false)
                    ->revealable()
                    ->same('password')
                    ->label('Confirm New Password')
                    ->placeholder('Re-enter new password if changing'),

                Forms\Components\TextInput::make('user_id')
                    ->label('User ID')
                    ->placeholder('Enter unique identifier')
                    ->maxLength(255)
                    ->unique(ignorable: fn($record) => $record), // enforce uniqueness in DB, but ignore when updating the same record
                Forms\Components\TextInput::make('phone_number')
                    ->label('Phone Number')
                    ->placeholder('Enter phone number')
                    ->tel() // adds input type="tel"
                    ->maxLength(20)
                    ->unique(ignorable: fn($record) => $record) // no duplicates

                /* Forms\Components\Toggle::make('active'), */

            ]);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        Log::info($data);
        return $data;
    }
    public static function CanEdit(): bool
    {
        return Auth::user()?->role !== Role::Cashier;
    }


    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\DeleteAction::make(),
    //     ];
    // }
}
