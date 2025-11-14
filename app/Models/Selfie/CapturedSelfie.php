<?php

namespace App\Models\Selfie;

use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class CapturedSelfie extends Model
{
    protected $guarded = ['id'];
    protected $appends = ['hashed_id'];

    public function selfie()
    {
        return $this->belongsTo(Selfie::class);
    }

    public function getHashedIdAttribute()
    {
        return Hashids::connection('main')->encode($this->id);
    }
}
