<?php

namespace App\Http\Requests\Client\Web;

use Illuminate\Foundation\Http\FormRequest;

class VerifyNumberRequest extends FormRequest
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
            'name' => 'required|string',
            'contact_list_id' => 'nullable|array',
            'segment_id' => 'nullable|array',
        ];
    }
    
}
