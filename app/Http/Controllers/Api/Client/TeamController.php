<?php
namespace App\Http\Controllers\Api\Client;
use App\Models\User;
use App\Models\ClientStaff;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\ApiReturnFormatTrait;
use App\Http\Resources\Api\TeamResource;
use Illuminate\Support\Facades\Validator;
use App\Repositories\Client\TeamRepository;

class TeamController extends Controller
{
    use ApiReturnFormatTrait;

    protected $teamRepo;

    public function __construct(TeamRepository $teamRepo)
    {
        $this->teamRepo = $teamRepo;
    }

    public function index(): \Illuminate\Http\JsonResponse
    {
        try {
            $user = jwtUser();
            $team = ClientStaff::where('client_id', $user->client_id)->with('user.lastActivity')->latest()->paginate(10);
            $data = [
                'team'     => TeamResource::collection($team),
                'paginate' => [
                    'total'         => $team->total(),
                    'current_page'  => $team->currentPage(),
                    'per_page'      => $team->perPage(),
                    'last_page'     => $team->lastPage(),
                    'prev_page_url' => $team->previousPageUrl(),
                    'next_page_url' => $team->nextPageUrl(),
                    'path'          => $team->path(),
                ],
            ];

            return $this->responseWithSuccess(__('data_retrieved_successfully'), $data);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], 500);
        }
    }

    public function store(Request $request, $id = null): \Illuminate\Http\JsonResponse
    {
        $baseRules = [
            'first_name' => 'required|string',
            'last_name'  => 'required|string',
            'email'      => 'required|email|unique:users,email,' . $id,
            'image'      => 'mimes:jpg,JPG,JPEG,jpeg,png,PNG,webp,WEBP|max:5120',
        ];
        if (! $id) {
            $baseRules['password'] = 'required|min:6|confirmed';
        } else {
            $baseRules['password'] = 'nullable|min:6|confirmed';
        }
        $validator = Validator::make($request->all(), $baseRules);
        if ($validator->fails()) {
            return $this->responseWithError(__('validation_failed'), $validator->errors(), 422);
        }

        try {
            $users                  = jwtUser();
            $total_team             = User::where('client_id', $users->client_id)->where('status', 1)->count();
            $team_limit             = $users->activeSubscription->team_limit;
            if ($total_team >= $team_limit) {
                $data = [
                    'status' => 'danger',
                    'error'  => __('insufficient_team_limit'),
                    'title'  => 'error',
                ];
                return response()->json($data);
            }
            $permissions            = [];
            foreach ($request->permissions ?? [] as $key => $value) {
                if ($value == 1) {
                    $permissions[] = $key;
                }
            }
            $request['permissions'] = $permissions;
            if ($id) {
                $this->teamRepo->update($request->all(), $id);
            } else {
                $this->teamRepo->store($request->all());
            }
            return $this->responseWithSuccess(__('created_successfully'));
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), [], 500);
        }
    }
}
