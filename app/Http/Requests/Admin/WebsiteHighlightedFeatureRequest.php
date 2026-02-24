<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class WebsiteHighlightedFeatureRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'logo'              => 'mimes:png,PNG|max:5120',
            'mini_title'        => 'required',
            'title'             => 'required',
            'description'       => 'nullable',
            'image'             => 'mimes:jpg,JPG,JPEG,jpeg,png,PNG,webp,WEBP|max:5120',
            'link'              => 'required',
            'lable'             => 'required',
        ];
    }
}
