<?php

namespace App\Models\Session;

use App\Enums\SelfieSessionStatus;
use App\Models\Selfie\Selfie;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SelfieSession extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'status' => SelfieSessionStatus::class,
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function selfie()
    {
        return $this->belongsTo(Selfie::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'prepare' => 'Preparing',
            'running' => 'Selfie in Progress',
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
