<?php

namespace App\Models\Session;

use App\Enums\StreamSessionStatus;
use App\Models\Stream\Streaming;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StreamSession extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'status' => StreamSessionStatus::class,
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function streaming()
    {
        return $this->belongsTo(Streaming::class);
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
