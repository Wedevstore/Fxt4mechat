<?php

namespace App\Http\Requests\Client;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class ContactUpdateRequest extends FormRequest
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
        $contactId = $this->id;
        $clientId = Auth::user()->client_id;
        return [
            'phone'      => [
                'required',
                // 'numeric',
                'min:11',
                // Rule::unique('contacts', 'phone')->ignore($contactId),
                Rule::unique('contacts', 'phone')
                ->ignore($contactId) // Ignore the current record during the update
                ->where(function ($query) use ($clientId) {
                    $query->where('client_id', $clientId);
                }),
            ],
            'country_id' => 'nullable|exists:countries,id',
            'name' => 'required|string'
        ];
    }
}
