<?php

namespace App\Models\Record;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Vinkla\Hashids\Facades\Hashids;

class RecordedVideo extends Model
{
    protected $guarded = ['id'];
    protected $appends = ['hashed_id'];

    public function recording()
    {
        return $this->belongsTo(Recording::class);
    }

    public function videoUserLike(): HasMany
    {
        return $this->hasMany(\App\Models\Video\VideoUserLike::class, 'recorded_video_id');
    }

    public function getHashedIdAttribute()
    {
        return Hashids::connection('main')->encode($this->id);
    }
}
