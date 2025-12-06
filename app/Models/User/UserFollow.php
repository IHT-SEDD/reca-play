<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserFollow extends Model
{
    protected $guarded = ['id'];

    public function follower(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'follower_id');
    }

    public function following(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'following_id');
    }
}
