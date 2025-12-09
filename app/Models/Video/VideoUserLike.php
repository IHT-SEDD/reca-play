<?php

namespace App\Models\Video;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoUserLike extends Model
{
    protected $guarded = ['id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Record\RecordedVideo::class, 'recorded_video_id');
    }
}
