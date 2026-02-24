<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactAttribute extends Model
{
    use HasFactory;

    protected $table    = 'contact_attributes';

    protected $fillable = [
        'title', 'client_id', 'status'
    ];

    protected $casts = [
        'type' => 'string',
    ];

    public function value()
    {
        return $this->hasOne(ContactAttributeValue::class, 'attribute_id');
    }

    public function scopeWithPermission($query)
    {
        if (auth()->user()->user_type != 'admin') {
            $client = auth()->user()->client;
            $query->where('client_id', $client->id);
        }
    }

    public function getTypeNameAttribute()
    {
        return config('static_array.custom_input_types')[$this->type] ?? 'Unknown';
    }



}
