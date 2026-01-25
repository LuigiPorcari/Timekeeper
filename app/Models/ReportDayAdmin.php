<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportDayAdmin extends Model
{
    protected $table = 'report_day_admin';

    protected $fillable = [
        'race_id',
        'user_id',
        'work_date',
        'van_cost',
        'hours_specialist',
        'hours_ordinary',
        'hours_ordinary_service',
        'hours_special_service',
    ];

    protected $casts = [
        'work_date' => 'date:Y-m-d',
        'van_cost' => 'decimal:2',
        'hours_specialist' => 'decimal:2',
        'hours_ordinary' => 'decimal:2',
        'hours_ordinary_service' => 'float',
        'hours_special_service' => 'float',

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