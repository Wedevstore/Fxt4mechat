<?php

namespace App\Http\Requests\Client\Web;

use Illuminate\Foundation\Http\FormRequest;

class TemplateStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'template_name'     => 'required|string|max:512',
            'message_type'       => 'required|string|in:NONE,TEXT,IMAGE,VIDEO,AUDIO,DOCUMENT',
            'message_body'      => 'required_if:message_type,text|string',
            'header_text'       => 'required_if:message_type,text|string|max:60',
            'header_image'      => 'required_if:message_type,image|image|mimes:jpeg,png,gif|max:5120',
            'header_video'      => 'required_if:message_type,video|mimes:mp4,avi,mov,wmv|file|max:16384',
            'header_document'   => 'required_if:message_type,document|file|mimes:pdf|max:5120',
        ];
    }
    public function messages()
    {
        return [
            'template_name.required' => 'Template name is required.',
            'template_name.max' => 'Template name can have a maximum of 512 characters.',
            'message_type.required' => 'Header type is required.',
            'message_type.in' => 'Header type must be one of none, text, image, video, audio, or document.',
            'message_body.required' => 'Message body is required.',
            'header_image.required_if' => 'Header image is required when the header type is "image".',
            'header_image.mimes' => 'Header image must be a JPEG, PNG, or GIF.',
            'header_image.max' => 'Header image size must not exceed 5120 KB.',
            'header_video.mimes' => 'Header video must be one of MP4, AVI, MOV, or WMV.',
            'header_video.max' => 'Header video size must not exceed 16384 KB.',
            'header_document.required_if' => 'Header document is required when the header type is "document".',
            'header_document.mimes' => 'Header document must be a PDF.',
            'header_document.max' => 'Header document size must not exceed 5120 KB.',
        ];
    }
}
