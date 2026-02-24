<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebsiteHighlightedFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'logo',
        'mini_title',
        'title',
        'description',
        'lable',
        'link',
        'image'
    ];

    protected $casts    = [
        'image'       => 'array',
        'logo'       => 'array',
        'description' => 'array',
    ];

    public function languages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WebsiteHighlightedFeatureLanguage::class);
    }

    public function language(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(WebsiteHighlightedFeatureLanguage::class)->where('lang', app()->getLocale());
    }

    public function getLangTitleAttribute()
    {
        return $this->language ? $this->language->title : $this->title;
    }

    public function getLangDescriptionAttribute()
    {
        return $this->language ? $this->language->description : $this->description;
    }
}
