<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportRaceDsc extends Model
{
    protected $table = 'report_race_dsc';

    protected $fillable = [
        'race_id',
        'user_id',
        'van_needed',
        'missed_meals',
        'apparecchiature',
        'confirmed',
    ];

    protected $casts = [
        'van_needed' => 'boolean',
        'missed_meals' => 'integer',
        'apparecchiature' => 'array',
        'confirmed' => 'boolean',
    ];

    public function race()
    {
        return $this->belongsTo(Race::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

