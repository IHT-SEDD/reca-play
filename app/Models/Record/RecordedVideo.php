<?php

namespace App\Models\Record;

use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class RecordedVideo extends Model
{
    protected $guarded = ['id'];
    protected $appends = ['hashed_id'];

    public function recording()
    {
        return $this->belongsTo(Recording::class);
    }

    public function getHashedIdAttribute()
    {
        return Hashids::connection('main')->encode($this->id);
    }
}
