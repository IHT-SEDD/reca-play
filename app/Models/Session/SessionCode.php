<?php

namespace App\Models\Session;

use App\Models\Master\Field;
use App\Models\Master\QrCode;
use App\Models\Master\Venue;
use App\Models\Record\Recording;
use App\Models\User;
use App\Enums\SessionCodeStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class SessionCode extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'status' => SessionCodeStatus::class,
        'expired_at' => 'datetime',
    ];

    public const Searchable = ['type', 'status', 'generated_code', 'name', 'start_time', 'end_time', 'used_at', 'created_at'];
    public const Unsearchable = ['id', 'user_id', 'qr_code_id', 'venue_id', 'field_id', 'recording_id', 'streaming_id', 'generated_by_user_id', 'session_token', 'expired_at', 'updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generate_by_user_id');
    }

    public function qrCode()
    {
        return $this->belongsTo(QrCode::class);
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function field()
    {
        return $this->belongsTo(Field::class);
    }

    public function recording()
    {
        return $this->belongsTo(Recording::class);
    }

    public function logs()
    {
        return $this->hasMany(SessionLog::class);
    }

    public function isValid(): bool
    {
        return $this->status !== SessionCodeStatus::Expired &&
            (!$this->expired_at || now()->lt($this->expired_at));
    }

    public function scopeNotExpired($query)
    {
        return $query->whereNot('status', SessionCodeStatus::Expired);
    }
}
