<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebsiteHighlightedFeatureLanguage extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_highlighted_feature_id',
        'lang',
        'mini_title',
        'title',
        'description',
        'lable'
    ];

    protected $casts    = [
        'description' => 'array',
    ];
}
