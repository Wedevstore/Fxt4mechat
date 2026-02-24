<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Activation;
use App\Models\PasswordRequest;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Traits\ImageTrait;
use App\Traits\SendMailTrait;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use ImageTrait, SendMailTrait;

    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function forgotPassword()
    {
        return view('auth.forget_password');
    }

    public function forgot(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);
        $user = User::where('email', $request->email)->first();
        if (! $user) {
            return redirect()->back()->with('error', __('user_not_found'));
        }
        try {
            $checkUserStatus = userAvailability($user);
            if (! $checkUserStatus['status']) {
                return back()->with('error', $checkUserStatus['message']);
            }
            DB::table('password_resets')->where('email', $user->email)->delete();
            $token           = Hash::make(Str::random(64)); // More secure token generation
            $resetLink       = url('/').'/password/reset/'.$token.'?email='.urlencode($user->email);
            DB::table('password_resets')->insert([
                'email'      => $user->email,
                'token'      => $token,
                'created_at' => Carbon::now(),
            ]);
            $data            = [
                'token'          => $token,
                'user'           => $user,
                'reset_link'     => $resetLink,
                'template_title' => 'password_reset',
            ];
            if (isMailSetupValid()) {
                $this->sendmail($user->email, 'emails.template_mail', $data);
                Toastr::success(__('receive__mail_password_hints'));
            }

            return redirect()->back()->with('success', __('receive__mail_password_hints'));
        } catch (Exception $e) {
            // Log::error('Password reset error: ', ['error' => $e->getMessage()]);
            Toastr::warning(__('an_error_occurred_while_processing_your_request'));

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function showResetPasswordForm($token)
    {
        $resetRecord = DB::table('password_resets')->where('token', $token)->first();
        if (! $resetRecord) {
            Toastr::error(__('invalid_password_reset_token'));

            return redirect()->route('login')->with('error', __('invalid_password_reset_token'));
        }

        return view('auth.forgetPasswordLink', ['token' => $token]);
    }

    public function submitResetPasswordForm(Request $request): RedirectResponse
    {
        try {
            $request->validate([
                'email'                 => 'required|email|exists:users,email',
                'password'              => 'required|string|min:6|confirmed',
                'password_confirmation' => 'required',
            ]);
            $resetRecord    = DB::table('password_resets')
                ->where('email', $request->email)
                ->first();
            if (! $resetRecord) {
                return back()->withInput()->with('error', __('invalid_token'));
            }
            $user           = User::where('email', $request->email)->firstOrFail();
            $user->password = Hash::make($request->password);
            $user->save();
            DB::table('password_resets')->where('email', $request->email)->delete();
            $data           = [
                'user'           => $user,
                'login_link'     => url('/login'),
                'template_title' => 'recovery_mail',
            ];
            if (isMailSetupValid()) {
                $this->sendmail($user->email, 'emails.template_mail', $data);
            }

            // Toastr::success(__('successfully_password_changed'));
            return redirect('/login')->with('success', __('successfully_password_changed'));
        } catch (\Exception $e) {
            // Toastr::error(__('an_error_occurred_while_processing_your_request'));
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function passwordUpdate(Request $request)
    {
        $request->validate([
            'password' => 'required|min:6|max:32|confirmed',
        ]);
        try {
            $user = User::where('email', $request->email)->first();
            $otp  = PasswordRequest::where('otp', $request->otp)->where('user_id', $user->id)->latest()->first();
            if ($otp) {
                $data           = [
                    'user'           => $user,
                    'login_link'     => url('/login'),
                    'template_title' => 'recovery_mail',
                ];
                if (isMailSetupValid()) {
                    $this->sendmail($request->email, 'emails.template_mail', $data);
                }
                $user->password = bcrypt($request->password);
                $user->save();
                $otp->delete();
                Toastr::success(__('successfully_password_changed'));

                return $this->logout($request);
            } else {
                Toastr::error(__('please_request_another_code'));

                return redirect()->back()->with('error', __('please_request_another_code'));
            }
        } catch (Exception $e) {
            Toastr::error(__($e->getMessage()));

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function verified(Request $request, $id): \Illuminate\Http\RedirectResponse
    {
        if (isDemoMode()) {
            Toastr::error(__('this_function_is_disabled_in_demo_server'));

            return back()->with('error', __('this_function_is_disabled_in_demo_server'));
        }
        try {
            $response = $this->userRepository->userVerified($request, $id);

            // Toastr::success(__($response['message']));
            return redirect()->back()->with('success', $response['message']);
        } catch (\Exception $e) {
            // Toastr::error($e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function activation($email, $code)
    {
        $user       = User::whereEmail($email)->first();
        $activation = Activation::where([['code', $code], ['user_id', $user->id]])->first();
        if ($activation) {
            if ($activation->completed == 1) {
                Toastr::success(__('your_account_has_been_already_activated'));

                return redirect()->route('login');
            } else {
                try {
                    DB::beginTransaction();
                    $activation->completed   = 1;
                    $activation->save();
                    $user->email_verified_at = now();
                    $user->status            = 1;
                    $user->save();
                    $data                    = [
                        'user'           => $user,
                        'login_link'     => url('/login'),
                        'template_title' => 'welcome_email',
                    ];
                    if (isMailSetupValid()) {
                        $this->sendmail($email, 'emails.template_mail', $data);
                    }
                    DB::commit();
                    Toastr::success(__('your_account_is_active_now'));

                    return redirect()->route('login')->with('success', __('your_account_is_active_now'));
                } catch (Exception $e) {
                    DB::rollBack();
                    Toastr::error(__($e->getMessage()));

                    return redirect()->route('login')->with('error', $e->getMessage());
                }
            }
        } else {
            Toastr::error(__('please_check_your_credential'));

            return redirect()->route('login')->with('error', __('please_check_your_credential'));
        }
    }

    public function changePassword()
    {
        return view('auth.change-password');
    }

    public function changePasswordUpdate(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password'         => 'required|min:6|max:32|confirmed',
        ]);
        $user = $this->userRepository->findByEmail(auth()->user()->email);
        if (Hash::check($request->current_password, $user->password)) {
            try {
                $user->password = bcrypt($request->password);
                $user->save();
                Toastr::success(__('successfully_password_changed'));

                return $this->logout($request);
            } catch (Exception $e) {
                Toastr::warning(__($e->getMessage()));

                return redirect()->back()->with('error', $e->getMessage());
            }
        } else {
            Toastr::warning(__('sorry_old_password_not_match'));

            return redirect()->back()->with('error', __('sorry_old_password_not_match'));
        }
    }
}
