<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebsiteUseCaseLanguage extends Model
{
    use HasFactory;

    protected $fillable = [
        'use_case_id',
        'lang',
        'title',
        'description',
    ];

    protected $casts    = [
        'description' => 'array',
    ];
}
