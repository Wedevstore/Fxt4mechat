<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SMSCampaign extends Model
{
    use SoftDeletes;

    protected $table = 'sms_campaigns';

    protected $primaryKey   = 'id';

    public $timestamps = false;

     // Define the fillable fields
     protected $fillable = [
        'contact_list_ids',
        'segment_ids',
        'name',
        'total_contact',
        'total_sent',
        'total_delivered',
        'total_failed',
        'status',
        'client_id',
        'country_id',
        'project_id',
        'template_id',
        'created_by',
        'updated_by',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    protected $casts    = [
        'contact_list_ids'      => 'array',
        'segment_ids'      => 'array',
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

    public function updatedby()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'area_id');
    }

    public function details()
    {
        return $this->hasMany(SMSHistory::class,'campaign_id');
    }
 
   
    public function template()
    {
        return $this->belongsTo(SMSTemplate::class);
    }

    public function contact_list()
    {
        return $this->hasOne(ContactsList::class, 'contact_list_id');
    }

    public function segment()
    {
        return $this->hasOne(Segment::class, 'segment_id');
    }

    
    public function client()
    {
        return $this->hasOne(Client::class, 'client_id');
    }


    public function scopeWithPermission($query)
    {
        if (auth()->user()->user_type != 'admin') {
            $client = auth()->user()->client;
            $query->where('client_id', $client->id);
        }
    }
}



