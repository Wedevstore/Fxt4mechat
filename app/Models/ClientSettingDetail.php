<?php

namespace App\Models;

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientSettingDetail extends Model
{
    protected $fillable = [
        'client_setting_id',
        'verified_name',
        'phone_number_id',
        'display_phone_number',
        'name_status',
        'certificate',
        'new_certificate',
        'quality_rating',
        'code_verification_status',
        'messaging_limit_tier',
        'number_status',
        'profile_info',
        'status',
    ];

    protected $casts    = [
        'profile_info'  => 'array',
        'data'          => 'array',
    ];

    public function clientSetting()
    {
        return $this->belongsTo(ClientSetting::class);
    }

}