<?php

namespace App\Models\Session;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QrSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'qr_code_id',
        'qr_code',
        'type',
        'qr_data',
        'session_token',
        'last_active_at',
    ];

    protected $casts = [
        'qr_data' => 'array',
        'last_active_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function qrCode()
    {
        return $this->belongsTo(\App\Models\Master\QrCode::class);
    }
}
