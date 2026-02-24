<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactAttributeValue extends Model
{
    use HasFactory;
    protected $table    = 'contact_attribute_values';
     // Add the $fillable property
     protected $fillable = [
        'contact_id',
        'attribute_id',
        'attr_value',
        'status'
    ];

    public function scopeWithPermission($query)
    {
        if (auth()->user()->user_type != 'admin') {
            $client = auth()->user()->client;
            $query->where('client_id', $client->id);
        }
    }

    
    public function attribute(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ContactAttribute::class,'attribute_id');
    }

}
