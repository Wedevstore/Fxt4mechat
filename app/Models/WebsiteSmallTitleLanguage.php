<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebsiteSmallTitleLanguage extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_small_title_id',
        'lang',
        'section',
        'title',
    ];

}
