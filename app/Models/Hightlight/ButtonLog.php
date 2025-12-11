<?php

namespace App\Models\Hightlight;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ButtonLog extends Model
{
    protected $guarded = ['id'];

    public function api(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Api::class, 'api_id', 'id');
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Field::class, 'field_id', 'id');
    }
}
