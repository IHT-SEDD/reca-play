<?php

namespace App\Models\Record;

use Illuminate\Database\Eloquent\Model;

class RecordedVideo extends Model
{
    protected $guarded = ['id'];

    protected $with = ['recording'];

    public function recording()
    {
        return $this->belongsTo(Recording::class);
    }
}
