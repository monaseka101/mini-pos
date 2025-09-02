<?php

namespace App\Helpers;

class Util
{
    public static function getDefaultAvatar($name): string
    {
        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=random';
    }

    public static function formatSaleId(string $state)
    {
        return str_pad($state, 5, '0', STR_PAD_LEFT);
    }
}
