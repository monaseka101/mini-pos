<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Role: string implements HasLabel, HasColor
{
    case Admin = 'admin';
    case Cashier = 'cashier';

    public function getLabel(): ?string
    {
        return $this->name;
    }

    public function getColor(): string|array|null
    {
        // match statement
        return match ($this) {
            self::Admin => 'success',
            self::Cashier => 'info'
        };
    }
}
