<?php

namespace App\Rules;

use App\Models\Contact;
use Illuminate\Contracts\Validation\Rule;

class UniquePhoneNumber implements Rule
{
    protected $clientId;

    public function __construct($clientId)
    {
        $this->clientId = $clientId;
    }

    public function passes($attribute, $value)
    {
        return ! Contact::where('client_id', $this->clientId)
            ->where(function ($query) use ($value) {
                $query->where('phone', $value)
                      ->orWhere('phone', "+" . $value);
            })
              // where('phone', $value)->
            ->exists();
    }

    public function message()
    {
        return __('the_phone_number_has_already_been_taken');
    }
}
