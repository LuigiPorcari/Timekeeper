<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportEntry extends Model
{
    protected $fillable = [
        'race_id',
        'user_id',
        'km',
        'pedaggi',
        'vitto',
        'alloggio',
        'spese_varie',
        'note',
        'confirmed',
    ];

    protected $casts = [
        'confirmed' => 'boolean',
        'km' => 'decimal:2',
        'pedaggi' => 'decimal:2',
        'vitto' => 'decimal:2',
        'alloggio' => 'decimal:2',
        'spese_varie' => 'decimal:2',
    ];

    public function race()
    {
        return $this->belongsTo(Race::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attachments()
    {
        return $this->hasMany(ReportAttachment::class);
    }
}
