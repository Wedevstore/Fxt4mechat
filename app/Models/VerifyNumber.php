<?php

namespace App\Models;

use App\Enums\VerifyNumberStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerifyNumber extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'contact_list_id', 'segment_id', 'client_id', 'device_id', 'contact_ids', 'total_verify', 'total_unverify', 'status'];

    protected $casts    = [
        'contact_ids' => 'array',
        'status'        => VerifyNumberStatusEnum::class,
        'contact_list_id' => 'array',
        'segment_id' => 'array',
        'contact_list_ids'      => 'array',
        'segment_ids'      => 'array',
        'total_contact' => 'integer',
        'total_verify' => 'integer',
        'total_unverify' => 'integer',
    ];


}
