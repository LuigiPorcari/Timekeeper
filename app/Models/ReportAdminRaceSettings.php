<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportAdminRaceSettings extends Model
{
    protected $table = 'report_admin_race_settings';

    protected $fillable = [
        'race_id',
        'coeff_km',
        'van_cost',
        'contributo_organizzativo',
        'apparecchiature_note',
        'spese_varie_gara',
    ];

    protected $casts = [
        'coeff_km' => 'float',
        'van_cost' => 'float',
        'contributo_organizzativo' => 'float',
        'spese_varie_gara' => 'float',
    ];

    public function race()
    {
        return $this->belongsTo(Race::class);
    }
}
