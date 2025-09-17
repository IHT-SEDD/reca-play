<?php

namespace App\Models\Record;

use App\Models\Master\Field;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Recording extends Model
{
    protected $guarded = ['id'];

    protected $with = ['field', 'recordingLog', 'recordedVideo'];

    public const Searchable = ['user_id', 'field_id', 'camera_id', 'video_name', 'start_time', 'end_time'];
    public const Unsearchable = ['id', 'duration', 'video_path', 'video_filename', 'video_size', 'created_at', 'updated_at'];

    public function field()
    {
        return $this->belongsTo(Field::class);
    }

    public function recordingLog()
    {
        return $this->hasMany(RecordingLog::class);
    }

    public function recordedVideo()
    {
        return $this->hasMany(RecordedVideo::class);
    }

    public function getDurationAttribute()
    {
        if (!$this->start_time || !$this->end_time) {
            return null;
        }

        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);

        // ambil selisih dalam detik (selalu positif)
        $diffInSeconds = abs($end->diffInSeconds($start));

        $hours   = floor($diffInSeconds / 3600);
        $minutes = floor(($diffInSeconds % 3600) / 60);
        $seconds = $diffInSeconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
}
