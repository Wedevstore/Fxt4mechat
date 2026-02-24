<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarmupContact extends Model
{
    use HasFactory;

    protected $fillable = ['client_id','warmup_id','name','phone_number','device_id','status'];
}
