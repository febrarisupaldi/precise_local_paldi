<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    public function login()
    {
        $credentials = request(['user_id', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function me()
    {
        return response()->json(["data" => auth()->user()]);
    }

    public function logout(Request $request)
    {
        $log = DB::table('log_user_login')
            ->where('log_id', $request->log_id)
            ->update(['logout_on' => DB::raw("sysdate()")]);
        if ($log == 1) {
            auth()->logout();
            return response()->json(['message' => 'Successfully logged out']);
        } else {
            return response()->json(['message' => 'Failed logged out']);
        }
    }

    // public function refresh()
    // {
    //     return $this->respondWithToken(auth()->refresh());
    // }

    protected function respondWithToken($token)
    {
        $log = DB::table('log_user_login')->insertGetId([
            'user_id' => $this->me()->getData()->data->user_id,
            'login_on' => DB::raw('sysdate()')
        ]);
        if ($log != '') {
            return response()->json([
                'access_token' => $token,
                'expires_in' => auth()->factory()->getTTL() * 60,
                'log_id' => $log
            ]);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }
}
