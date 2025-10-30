<?php

namespace App\Models\Stream;

use App\Enums\StreamingLogStatus;
use Illuminate\Database\Eloquent\Model;

class StreamingLog extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'status' => StreamingLogStatus::class,
    ];

    public const Searchable = ['qr_code', 'status'];
    public const Unsearchable = ['id', 'streaming_id', 'created_at', 'updated_at'];

    public function streaming()
    {
        return $this->belongsTo(Streaming::class);
    }
}
