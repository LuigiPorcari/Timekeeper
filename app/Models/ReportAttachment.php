<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportAttachment extends Model
{
    protected $fillable = [
        'report_entry_id',
        'file_path',
        'original_name',
    ];

    public function reportEntry()
    {
        return $this->belongsTo(ReportEntry::class);
    }
}
