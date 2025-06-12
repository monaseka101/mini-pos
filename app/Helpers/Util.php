<?php

namespace App\Helpers;

class Util
{
    public static function getDefaultAvatar($name): string
    {
        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=random';
    }
}
