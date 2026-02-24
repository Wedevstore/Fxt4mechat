<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'client_id',
        'message_type',
        'message',
        'media_url',
        'file_name',
        'mimetype',
        'latitude',
        'longitude',
        'status'
    ];
}
