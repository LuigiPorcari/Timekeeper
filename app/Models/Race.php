<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Race extends Model
{
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
    public function records()
    {
        return $this->hasMany(Record::class);
    }

    protected $fillable = [
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
