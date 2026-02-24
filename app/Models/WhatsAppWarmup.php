<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppWarmup extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'device_id',
        'name',
        'day',
        'messages_sent_today',
        'is_completed',
        'last_sent_at',
        'status'
    ];

    protected $casts = [
        'messages_sent_today' => 'integer',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id');
    }

    public function messages()
    {
        return $this->hasMany(WhatsAppWarmupMessage::class, 'warmup_id');
    }
}
