<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebsiteFlowBuilderLanguage extends Model
{
    use HasFactory;
    protected $fillable = [
        'website_flow_builder_id',
        'lang',
        'title',
        'description',
    ];

    protected $casts    = [
        'description' => 'array',
    ];
}
