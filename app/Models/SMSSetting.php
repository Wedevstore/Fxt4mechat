<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SMSSetting extends Model
{
    use HasFactory; 
    protected $table = 'sms_settings';
    public $timestamps = false;
    protected $fillable = ['key', 'value', 'client_id', 'created_by'];
    protected $casts    = [
        // 'value' => 'array',
    ];

    public function scopeWithPermission($query)
    {
        if (auth()->user()->user_type != 'admin') {
            $client = auth()->user()->client;
            $query->where('client_id', $client->id);
        }
    }
}
