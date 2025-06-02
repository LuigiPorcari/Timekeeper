<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Availability extends Model
{
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
    protected $fillable = [
        'date_of_availability'
    ];
}
