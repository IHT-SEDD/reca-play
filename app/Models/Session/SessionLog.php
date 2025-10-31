<?php

namespace App\Models\Session;

use App\Enums\SessionLogStatus;
use App\Models\Master\QrCode;
use App\Models\Record\Recording;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionLog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'status' => SessionLogStatus::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function qrCode()
    {
        return $this->belongsTo(QrCode::class);
    }

    public function sessionCode()
    {
        return $this->belongsTo(SessionCode::class);
    }

    public function recording()
    {
        return $this->belongsTo(Recording::class);
    }

    public function getDurationAttribute(): ?int
    {
        if ($this->start_time && $this->end_time) {
            return $this->end_time->diffInSeconds($this->start_time);
        }
        return null;
    }

    public function getDurationFormattedAttribute(): ?string
    {
        if ($this->duration === null) {
            return null;
        }

        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;

        return sprintf('%02d:%02d', $minutes, $seconds);
    }
}
