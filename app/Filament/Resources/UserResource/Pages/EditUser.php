<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Enums\Role;
use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms;
use Filament\Forms\Form;
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
                    ->columnSpanFull(),

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
                Forms\Components\Toggle::make('active'),
            ]);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        Log::info($data);
        return $data;
    }


    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\DeleteAction::make(),
    //     ];
    // }
}
