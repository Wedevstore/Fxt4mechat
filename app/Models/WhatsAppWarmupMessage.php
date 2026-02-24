<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppWarmupMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'warmup_id',
        'device_id',
        'warmup_contact_id',
        'phone_number',
        'message',
        'status',
        'response',
    ];

    public function warmup()
    {
        return $this->belongsTo(WhatsAppWarmup::class, 'warmup_id');
    }
}
