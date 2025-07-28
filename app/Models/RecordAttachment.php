<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecordAttachment extends Model
{
    protected $fillable = ['file_path', 'original_name'];

    public function record()
    {
        return $this->belongsTo(Record::class);
    }
}
