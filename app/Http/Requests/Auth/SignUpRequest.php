<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SignUpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->phone) {
            $phone = preg_replace('/[^\d+]/', '', $this->phone);

            // Ensure starts with +
            if (!str_starts_with($phone, '+')) {
                $phone = '+' . $phone;
            }

            $this->merge([
                'phone' => $phone, 
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'first_name'   => ['required', 'string', 'max:255'],
            // 'last_name'    => ['required', 'string', 'max:255'],
            // 'company_name' => ['required', 'string', 'max:255'],
            'email'        => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            // 'phone'        => ['required', 'string', 'phone', 'max:25', 'unique:users,phone', 'regex:/^\+?[1-9]\d{7,14}$/'],
            'password'     => ['required', 'string', 'min:6'],
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            // Custom messages for first name
            'first_name.required'   => __('first_name_is_required'),
            'first_name.string'     => __('first_name_must_be_a_string'),
            'first_name.max'        => __('first_name_must_not_exceed_255_characters'),
    
            // Custom messages for last name
            'last_name.required'    => __('last_name_is_required'),
            'last_name.string'      => __('last_name_must_be_a_string'),
            'last_name.max'         => __('last_name_must_not_exceed_255_characters'),
    
            // Custom messages for company name
            'company_name.required' => __('company_name_is_required'),
            'company_name.string'   => __('company_name_must_be_a_string'),
            'company_name.max'      => __('company_name_must_not_exceed_255_characters'),
    
            // Custom messages for email
            'email.required'        => __('email_is_required'),
            'email.string'          => __('email_must_be_a_string'),
            'email.email'           => __('email_must_be_a_valid_email_address'),
            'email.max'             => __('email_must_not_exceed_255_characters'),
            'email.unique'          => __('email_already_taken'),
    
            // Custom messages for phone
            'phone.required'        => __('phone_number_is_required'),
            'phone.string'          => __('phone_must_be_a_string'),
            'phone.phone'           => __('phone_must_be_a_valid_phone_number'),
            'phone.max'             => __('phone_must_not_exceed_20_characters'),
            'phone.unique'          => __('phone_already_taken'),
    
            // Custom messages for password
            'password.required'     => __('password_is_required'),
            'password.string'       => __('password_must_be_a_string'),
            'password.min'          => __('password_must_be_at_least_6_characters'),
    
            // Custom message for reCAPTCHA
            'recaptcha.gte'         => __('please_verify_that_you_are_not_a_robot'),
        ];
    }
    
    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();
        $user = User::where('email', $this->email)->first();
        if (isset($user->email) && $user->status == 0) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => trans('auth.inactive'),
            ]);
        } else {
            $check_user_status = userAvailability($user);

            if (! $check_user_status['status']) {
                throw ValidationException::withMessages([
                    'email' => $check_user_status['message'],
                ]);
            }
            if (! Auth::attempt(array_merge($this->only('email', 'password'), ['status' => 1]), $this->boolean('remember'))) {
                RateLimiter::hit($this->throttleKey());
                throw ValidationException::withMessages([
                    'email' => trans('auth.failed'),
                ]);
            }
            RateLimiter::clear($this->throttleKey());
        }
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());
    }
}
