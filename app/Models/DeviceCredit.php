<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceCredit extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'total_credits',
        'used_credits'
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function getRemainingCreditsAttribute()
    {
        return $this->total_credits - $this->used_credits;
    }

}
