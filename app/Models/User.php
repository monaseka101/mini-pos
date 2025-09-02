<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Role;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Auth\Passwords\CanResetPassword;

class User extends Authenticatable implements HasAvatar, FilamentUser, CanResetPasswordContract
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, CanResetPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'avatar_url',
        'password',
    ];
    protected $casts = [
        'role' => Role::class,
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class
        ];
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url
            ? Storage::url($this->avatar_url)
            : null;
    }

    public static function getDefaultAvatar($name): string
    {
        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=random';
    }

    // php artisan vendor:publish --tag=filament-config

    public function canAccessPanel(Panel $panel): bool
    {
        // Only allow active users to access the admin panel
        return $this->active == true;
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }


    public function getRoleEnum(): ?Role
    {
        if (!isset($this->role)) {
            return null;
        }

        // If it's already a Role enum, return it directly
        if ($this->role instanceof Role) {
            return $this->role;
        }

        // Otherwise, convert string to enum
        return Role::from($this->role);
    }
}
