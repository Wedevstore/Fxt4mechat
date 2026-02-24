<?php

namespace App\Models;

use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SMSTemplate extends Model
{
    use HasFactory;

    protected $table = 'sms_templates';
    protected $fillable = [
        'title',
        'template_id',
        'body',
        'short_codes',
        'status',
        'client_id',
        'created_by', 
        'updated_by',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        // 'status' => StatusEnum::class,
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
    
    public static function boot()
    {
        parent::boot();
        static::creating(function($model)
        {
           $model->client_id = auth()->user()->client ? auth()->user()->client->id : null;
           $model->created_by = auth()->user() ? auth()->user()->id : null;
           $model->created_at = date('Y-m-d H:i:s');
        });

        static::updating(function($model)
        {
           $model->updated_by = auth()->user() ? auth()->user()->id : null;
           $model->updated_at = date('Y-m-d H:i:s');
        });
    }

    public function scopeWithPermission($query)
    {
        if (auth()->user()->user_type != 'admin') {
            $client = auth()->user()->client;
            $query->where('client_id', $client->id);
        }
    }

}