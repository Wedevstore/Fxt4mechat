<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'device_id',
        'type',
        'credits',
        'message_type',
        'reference',
        'balance_after',
        'description'
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
