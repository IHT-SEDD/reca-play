<?php

namespace App\Models\Record;

use App\Enums\RecordingLogStatus;
use Illuminate\Database\Eloquent\Model;

class RecordingLog extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'status' => RecordingLogStatus::class,
    ];

    public const Searchable = ['qr_code', 'status'];
    public const Unsearchable = ['id', 'recording_id', 'created_at', 'updated_at'];

    public function recording()
    {
        return $this->belongsTo(Recording::class);
    }
}
