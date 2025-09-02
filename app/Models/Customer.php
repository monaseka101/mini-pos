<?php

namespace App\Models;

use App\Enums\Gender;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory;

    protected function casts()
    {
        return [
            'gender' => Gender::class,
            // 'date_of_birth' => 'date:d-m-Y'
        ];
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
