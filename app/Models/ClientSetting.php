<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'user_id',
        'access_token',
        'app_id',
        'phone_number_id',
        'business_account_id',
        'business_account_name',
        'is_connected',
        'type',
        'bot_id',
        'name',
        'username',
        'webhook_verified',
        'scopes',
        'granular_scopes',
        'data_access_expires_at',
        'expires_at',
        'fb_user_id',
        'token_verified',
        'json_data',
        'status',
    ];


    protected $casts    = [
        'scopes' => 'array',
        'json_data' => 'array',
        'granular_scopes' => 'array',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function staff()
    {
        return $this->hasMany(ClientStaff::class, 'client_id', 'id');
    } 
    public function scopeWithPermission($query)
    {
        if (auth()->user()->user_type != 'admin') {
            $client = auth()->user()->client;
            $query->where('client_id', $client->id);
        }
    }

    public function details()
    {
        return $this->hasMany(ClientSettingDetail::class);
    }
}
