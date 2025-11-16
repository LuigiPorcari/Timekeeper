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
        'ente_fatturazione',
        'date_start',
        'date_end',
        'programma_allegato',
        'note',
        'type',
        'preventivo_da_aggiungere',
    ];

    protected function casts(): array
    {
        return [
            'specialization_of_race' => 'array',
            'date_of_race' => 'date',
            'date_start' => 'date',
            'date_end' => 'date',
            'preventivo_da_aggiungere' => 'boolean',
        ];
    }
}
