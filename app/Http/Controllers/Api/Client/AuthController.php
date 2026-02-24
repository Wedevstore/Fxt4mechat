<?php
namespace App\Http\Controllers\Api\Client;
use App\Http\Controllers\Controller;
use App\Models\Timezone;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Traits\ApiReturnFormatTrait;
use App\Traits\ImageTrait;
use App\Traits\SendMailTrait;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    use ApiReturnFormatTrait,ImageTrait,SendMailTrait;

    protected $repo;

    public function __construct(UserRepository $repo)
    {
        $this->userRepository = $repo;
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->responseWithError(__('validation_failed'), $validator->errors(), 422);
        }
        try {
            $user              = User::with('client')->where('email', $request->email)->where('user_type', 'client-staff')->first();

            $check_user_status = userAvailability($user);

            if (! $check_user_status['status']) {
                return $this->responseWithError($check_user_status['message'], [], $check_user_status['code']);
            }
            $credentials       = $request->only('email', 'password');
            try {
                if (! $token = JWTAuth::attempt($credentials)) {
                    return $this->responseWithError(__('invalid_credentials'), [], 401);
                }
            } catch (JWTException $e) {
                return $this->responseWithError($e->getMessage(), [], 422);
            } catch (\Exception $e) {
                return $this->responseWithError($e->getMessage(), [], 500);
            }
            Auth::attempt($credentials);
            //dd($user);
            return $this->responseWithSuccess(__('login_successfully'), authData($user, $token));
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], 500);
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status == Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
    }

    public function forgotPassword(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required_without:phone|exists:users,email',
        ]);
        if ($validator->fails()) {
            return $this->responseWithError(__('required_field_missing'), $validator->errors(), 422);
        }

        try {
            $otp               = rand(1000, 9999);

            $user              = $this->userRepository->findByEmail($request->email);
            $check_user_status = userAvailability($user);

            if (! $check_user_status['status']) {
                return $this->responseWithError($check_user_status['message'], [], $check_user_status['code']);
            }
            $verify            = DB::table('password_resets')->where('email', $request->email)->first();

            if ($verify && now() < Carbon::parse($verify->created_at)->addMinutes(2)) {
                return $this->responseWithError(__('otp_already_sent'), [], 500);
            }

            DB::table('password_resets')->where('email', $request->email)->delete();

            DB::table('password_resets')->insert([
                'email'      => $request->email,
                'token'      => $otp,
                'created_at' => now(),
            ]);

            $data['user']      = $user;
            $data['otp']       = $otp;
            $data['subject']   = 'Reset Password';

            if (arrayCheck('phone', $data)) {
                $msg = $this->sendSMS($request->phone, 'forgot_password', $otp);

                if (! $msg) {
                    return $this->responseWithError($msg, [], 500);
                }
            } else {
                $this->sendmail($request->email, 'emails.api.forgot_password', $data);
            }

            return $this->responseWithSuccess((string) $otp);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage());
        }
    }

    public function verifyOtp(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required_without:phone|exists:users,email',
            'otp'   => 'required',
        ]);
        if ($validator->fails()) {
            return $this->responseWithError(__('required_field_missing'), $validator->errors(), 422);
        }
        DB::beginTransaction();
        try {
            $otp = DB::table('password_resets')->where('token', $request->otp)->where('email', $request->email)->latest()->first();

            if ($otp) {
                return $this->responseWithSuccess(__('otp_verified_successfully'));
            } else {
                return $this->responseWithError(__('otp_doesnt_match'), [], 404);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->responseWithError($e->getMessage(), [], 500);
        }
    }

    public function resetPassword(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required_without:phone|exists:users,email',
            'password' => 'required|confirmed|min:6',
            'otp'      => 'required',
        ]);
        if ($validator->fails()) {
            return $this->responseWithError(__('required_field_missing'), $validator->errors(), 422);
        }

        DB::beginTransaction();
        try {
            $otp            = DB::table('password_resets')->where('token', $request->otp)->where('email', $request->email)->latest()->first();

            if (! $otp) {
                return $this->responseWithError(__('otp_doesnt_found'), [], 404);
            }

            $user           = $this->userRepository->findByEmail($otp->email);
            $user->password = bcrypt($request->password);
            $user->save();

            DB::table('password_resets')->where('email', $request->email)->delete();

            DB::commit();

            return $this->responseWithSuccess(__('password_has_been_changed'));

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['errors' => 'Something Went wrong']);
        }
    }

    public function profileUpdate(Request $request, UserRepository $userRepository): \Illuminate\Http\JsonResponse
    {
        if (isDemoMode()) {
            $data = [
                'status' => 'danger',
                'error'  => __('this_function_is_disabled_in_demo_server'),
                'title'  => 'error',
            ];

            return response()->json($data);
        }

        $userId    = jwtUser()->id;

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email,'.$userId.',id',
            'phone'      => 'required|unique:users,phone,'.$userId.',id',
        ]);

        if ($validator->fails()) {
            return $this->responseWithError(__('required_field_missing'), $validator->errors(), 422);
        }

        try {
            $userRepository->update($request->all(), $userId);

            return $this->responseWithSuccess(__('profile_update_successfully'));
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage());
        }
    }

    public function profile(UserRepository $userRepository)
    {
        try {
            $user = $userRepository->find(jwtUser()->id);

            $data = [
                'id'         => $user->id,
                'name'       => $user->name,
                'first_name' => $user->first_name,
                'last_name'  => nullCheck($user->last_name),
                'email'      => $user->email,
                'phone'      => nullCheck($user->phone),
                'address'    => nullCheck($user->address),
                'image'      => $user->profile_pic,
                'time_zones' => Timezone::all(),
            ];

            return $this->responseWithSuccess(__('profile_retrieved_successfully'), $data);
        } catch (\Exception $e) {
            return $this->responseWithError('An unexpected error occurred. Please try again later.', [], 500);
        }
    }

    public function logout(): JsonResponse
    {
        try {
            JWTAuth::getToken();
            JWTAuth::parseToken()->invalidate(true);
            return $this->responseWithSuccess(__('logout_successfully'));
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], 500);
        }
    }
}
