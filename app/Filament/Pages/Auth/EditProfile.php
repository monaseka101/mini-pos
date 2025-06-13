<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class EditProfile extends BaseEditProfile
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getProfileSection(),
                $this->getPasswordSection(),
            ])
            ->operation('edit')
            ->model($this->getUser())
            ->statePath('data');
    }

    protected function getProfileSection(): Component
    {
        return Section::make('Profile Information')
            ->description('Update your profile information and avatar.')
            ->schema([
                FileUpload::make('avatar_url')
                    ->label('Profile Picture')
                    ->avatar()
                    ->image()
                    ->imageEditor()
                    ->circleCropper()
                    ->directory('avatars')
                    ->visibility('public')
                    ->imageResizeMode('cover')
                    ->imageCropAspectRatio('1:1')
                    ->imageResizeTargetWidth('300')
                    ->imageResizeTargetHeight('300')
                    ->maxSize(2048)
                    ->helperText('Upload a profile picture (JPEG, PNG, or WebP, max 2MB)')
                    ->columnSpanFull()
                    ->extraAttributes([
                        'class' => 'mb-6'
                    ]),

                TextInput::make('name')
                    ->label('Full Name')
                    ->required()
                    ->maxLength(255)
                    ->autocomplete('name')
                    ->prefixIcon('heroicon-m-user')
                    ->columnSpanFull(),

                // TextInput::make('email')
                //     ->label('Email Address')
                //     ->email()
                //     ->required()
                //     ->maxLength(255)
                //     ->autocomplete('email')
                //     ->unique(ignoreRecord: true)
                //     ->prefixIcon('heroicon-m-envelope')
                //     ->columnSpanFull(),
            ])
            ->columns(1)
            ->icon('heroicon-o-user-circle')
            ->aside();
    }

    protected function getPasswordSection(): Component
    {
        return Section::make('Update Password')
            ->description('Leave empty to keep your current password unchanged.')
            ->schema([
                TextInput::make('current_password')
                    ->revealable()
                    ->label('Current Password')
                    ->password()
                    ->autocomplete('current-password')
                    ->prefixIcon('heroicon-m-lock-closed')
                    ->helperText('Required only when changing password')
                    ->requiredWith('password')
                    // checked is the current input password matches the authenticated user's password
                    ->currentPassword()
                    ->columnSpanFull(),

                TextInput::make('password')
                    ->label('New Password')
                    ->revealable()
                    ->password()
                    ->rule(Password::default())
                    ->autocomplete('new-password')
                    ->dehydrateStateUsing(fn ($state) => $state ? Hash::make($state) : null)
                    ->live(debounce: 500)
                    ->same('password_confirmation')
                    ->prefixIcon('heroicon-m-key')
                    ->helperText('Leave empty to keep current password')
                    ->columnSpanFull(),

                TextInput::make('password_confirmation')
                    ->label('Confirm New Password')
                    ->same('password')
                    ->password()
                    ->revealable()
                    ->dehydrated(false)
                    ->autocomplete('new-password')
                    ->prefixIcon('heroicon-m-key')
                    ->requiredWith('password')
                    ->helperText(text: 'Required when setting new password')
                    ->columnSpanFull(),
            ])
            ->columns(1)
            ->icon('heroicon-o-lock-closed')
            ->collapsible()
            ->collapsed()
            ->aside();
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Remove password confirmation from data
        unset($data['password_confirmation']);
        unset($data['current_password']);

        // Only update password if it's provided and not empty
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $record->update($data);

        return $record;
    }

    protected function getSavedNotificationMessage(): ?string
    {
        return 'Your profile has been updated successfully.';
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Don't fill password fields
        $data['current_password'] = '';
        $data['password'] = '';
        $data['password_confirmation'] = '';

        return $data;
    }

    protected function afterSave(): void
    {
        // Clear the password fields after saving
        $this->data['current_password'] = '';
        $this->data['password'] = '';
        $this->data['password_confirmation'] = '';
    }

    public function getTitle(): string
    {
        return 'Edit Profile';
    }

    public function getHeading(): string
    {
        return 'Edit Profile';
    }

    public function getSubheading(): ?string
    {
        return 'Manage your profile information and account settings.';
    }
}
