<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportDayDsc extends Model
{
    protected $table = 'report_day_dsc';

    protected $fillable = [
        'race_id',
        'user_id',
        'work_date',

        'morning_start',
        'morning_end',
        'afternoon_start',
        'afternoon_end',

        'van_needed',
        'missed_meals',
        'apparecchiature',

        'confirmed',
    ];

    protected $casts = [
        'work_date' => 'date:Y-m-d',
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
