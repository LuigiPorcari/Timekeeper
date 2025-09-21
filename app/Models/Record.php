<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    protected $fillable = [
        'daily_service',
        'special_service',
        'rate_documented',
        'km_documented',
        'amount_documented',
        'travel_ticket_documented',
        'food_documented',
        'accommodation_documented',
        'various_documented',
        'food_not_documented',
        'daily_allowances_not_documented',
        'special_daily_allowances_not_documented',
        'total',
        'description',
        'user_id',
        'race_id',
        'confirmed',
        'type',
        'euroKM',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function race()
    {
        return $this->belongsTo(Race::class);
    }

    public function attachments()
    {
        return $this->hasMany(RecordAttachment::class);
    }

    protected function casts(): array
    {
        return [
            'euroKM' => 'decimal:2', // 2 decimali fissi
        ];
    }
}
