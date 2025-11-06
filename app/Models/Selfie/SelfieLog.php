<?php

namespace App\Models\Selfie;

use App\Enums\SelfieLogStatus;
use Illuminate\Database\Eloquent\Model;

class SelfieLog extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'status' => SelfieLogStatus::class,
    ];

    public const Searchable = ['qr_code', 'status'];
    public const Unsearchable = ['id', 'selfie_id', 'created_at', 'updated_at'];

    public function selfie()
    {
        return $this->belongsTo(Selfie::class);
    }
}
