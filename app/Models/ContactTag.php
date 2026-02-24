<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactTag extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'contact_id',
        'tag_id',
        'status',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function tag()
    {
        return $this->belongsTo(ClientTag::class, 'tag_id');
    }
}
