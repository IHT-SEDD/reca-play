<?php

namespace App\Models\Record;

use Illuminate\Database\Eloquent\Model;

class RecordingLog extends Model
{
    protected $guarded = ['id'];

    protected $with = ['recording'];

    public const Searchable = ['user_device_ip', 'qr_code', 'status'];
    public const Unsearchable = ['id', 'recording_id', 'created_at', 'updated_at'];

    public function recording()
    {
        return $this->belongsTo(Recording::class);
    }
}
