<?php

namespace App\Models\Session;

use App\Models\Record\Recording;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecordSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'recording_id',
        'qr_code',
        'status',
        'ip_address',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function recording()
    {
        return $this->belongsTo(Recording::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'prepare' => 'Preparing',
            'running' => 'Recording in Progress',
            'done'    => 'Finished',
            default   => ucfirst($this->status ?? 'Unknown'),
        };
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByQr($query, string $qr)
    {
        return $query->where('qr_code', $qr);
    }
}
