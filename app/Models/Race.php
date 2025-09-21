<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Race extends Model
{
    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('is_leader')->withTimestamps();
    }
    public function records()
    {
        return $this->hasMany(Record::class);
    }
    public function pivotUsers()
    {
        return $this->belongsToMany(User::class)->withPivot('is_leader')->withTimestamps();
    }

    protected $fillable = [
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
    ];
    protected function casts(): array
    {
        return [
            'specialization_of_race' => 'array',
            'date_of_race' => 'date',
            'date_start' => 'date',
            'date_end' => 'date',
        ];
    }

}
