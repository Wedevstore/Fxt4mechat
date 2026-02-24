<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'phone_number',
        'whatsapp_session',
        'jid',
        'status',
        'account_protection',
        'message_logging',
        'read_incoming',
        'webhook_url',
        'connected_at',
        'disconnected_at',
        'active_for_chat',
        'active_for_chat_time'
    ];

    public function lastActiveChatDevice()
    {
        return $this->hasOne(Device::class)
            ->where('active_for_chat', 1)
            ->orderByDesc('active_for_chat_time');
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function credit()
    {
        return $this->hasOne(DeviceCredit::class);
    }

    public function creditTransactions()
    {
        return $this->hasMany(CreditTransaction::class);
    }

    public function availableCredits(): int
    {
        if (!$this->credit) {
            return 0;
        }

        return max(
            (int) $this->credit->total_credits - (int) $this->credit->used_credits,
            0
        );
    }

    
}
