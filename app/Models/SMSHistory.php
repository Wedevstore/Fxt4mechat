<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SMSHistory extends Model
{
    use HasFactory;

    protected $table = 'sms_history';

    protected $fillable = [
        'message_id',
        'body',
        'schedule_at',
        'send_at',
        'delivered_at',
        'status',
        'error',
        'contact_id',
        'client_id',
        'campaign_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        // 'status' => MessageStatusEnum::class,
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function($model)
        {
           $model->created_by = auth()->user() ? auth()->user()->id : null;
           $model->created_at = date('Y-m-d H:i:s');
        });

        static::updating(function($model)
        {
           $model->updated_by = auth()->user() ? auth()->user()->id : null;
           $model->updated_at = date('Y-m-d H:i:s');
        });
    }

    public function campaign()
    {
        return $this->belongsTo(SMSCampaign::class, 'campaign_id');
    }

    public function sms_conatct()
    {
        return $this->belongsTo(Contact::class,'contact_id');
    }



}