<?php

namespace App\Models\Record;

use App\Models\Master\Camera;
use App\Models\Master\Field;
use App\Models\Session\SessionCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Vinkla\Hashids\Facades\Hashids;

class Recording extends Model
{
    protected $guarded = ['id'];
    protected $appends = ['duration_formatted', 'hashed_id'];

    public const Searchable = ['user_id', 'field_id', 'camera_id', 'video_name', 'start_time', 'end_time'];
    public const Unsearchable = ['id', 'duration', 'video_path', 'video_filename', 'video_size', 'created_at', 'updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function field()
    {
        return $this->belongsTo(Field::class);
    }

    public function camera()
    {
        return $this->belongsTo(Camera::class);
    }

    public function recordingLog()
    {
        return $this->hasMany(RecordingLog::class);
    }

    public function recordedVideo()
    {
        return $this->hasMany(RecordedVideo::class);
    }

    public function sessionCode()
    {
        return $this->belongsTo(SessionCode::class);
    }

    public function getDurationAttribute()
    {
        return $this->attributes['duration'] ?? null;
    }

    public function getDurationFormattedAttribute()
    {
        if (!$this->start_time || !$this->end_time) {
            return null;
        }

        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);

        $diffInSeconds = abs($end->diffInSeconds($start));

        $hours   = floor($diffInSeconds / 3600);
        $minutes = floor(($diffInSeconds % 3600) / 60);
        $seconds = $diffInSeconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    public function getHashedIdAttribute()
    {
        return Hashids::connection('main')->encode($this->id);
    }
}
