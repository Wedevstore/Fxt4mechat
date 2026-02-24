<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebsiteNavMoreLanguage extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_nav_more_id',
        'lang',
        'title',
        'description',
    ];

    protected $casts    = [
        'description' => 'array',
    ];
}
