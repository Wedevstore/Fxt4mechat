<?php

namespace App\Http\Requests\Auth;

use App\Models\Client;
use App\Models\ClientStaff;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            // 'g-recaptcha-response' => ['required', 'gte:1'],

        ];

        // if (setting('is_recaptcha_activated') && setting('recaptcha_site_key') && setting('recaptcha_secret')) {
        //     $rules['g-recaptcha-response'] = ['required', 'gte:1'];
        // }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'email.required'                => __('email_is_required'),
            'email.email'                   => __('please_provide_a_valid_email_address'),
            'password.required'             => __('password_is_required'),
            'g-recaptcha-response.required' => __('please_verify_that_you_are_not_a_robot'),
            'g-recaptcha-response.gte'      => __('please_verify_that_you_are_not_a_robot'),
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
    
            // if (! Auth::attempt(array_merge($this->only('email', 'password'), ['status' => 1]), $this->boolean('remember'))) {
            //     RateLimiter::hit($this->throttleKey());
            //     throw ValidationException::withMessages([
            //         'email' => trans('auth.failed'),
            //     ]);
            // }
            // RateLimiter::clear($this->throttleKey());

            // Check if user is a ClientStaff
            $client_stuff_exist = ClientStaff::where('user_id', $user->id)->first();
            // dd($client_stuff_exist);
            if($client_stuff_exist == null){
                if (!Auth::attempt(array_merge($this->only('email', 'password'), ['status' => 1]), $this->boolean('remember'))) {
                    RateLimiter::hit($this->throttleKey());
                    throw ValidationException::withMessages([
                        'email' => trans('auth.failed'),
                    ]);
                }
                RateLimiter::clear($this->throttleKey());
            }else{
                $client_exist = null; // Default to null

                if ($client_stuff_exist) {
                    $client_exist = Client::where('id', $client_stuff_exist->client_id)->first();
                }

                // Ensure `$client_exist` exists before accessing `status`
                if ($client_exist && $client_exist->status == 1) {
                    // Proceed with authentication
                    if (!Auth::attempt(array_merge($this->only('email', 'password'), ['status' => 1]), $this->boolean('remember'))) {
                        RateLimiter::hit($this->throttleKey());
                        throw ValidationException::withMessages([
                            'email' => trans('auth.failed'),
                        ]);
                    }
                    RateLimiter::clear($this->throttleKey());
                } else {
                    throw ValidationException::withMessages([
                        'email' => 'Client is inactive please contact to admin.',
                    ]);
                }
            }
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
