<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RaceTemp extends Model
{
    protected $fillable = [
        'email',
        'name',
        'place',
        'date_of_race',
        'specialization_of_race',
    ];

    protected function casts(): array
    {
        return [
            'specialization_of_race' => 'array',
            'date_of_race' => 'date',
        ];
    }
}
