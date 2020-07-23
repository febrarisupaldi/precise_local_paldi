<?php

namespace App\Http\Controllers\Api\Application;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\Auth\AuthController;

class LogController extends Controller
{
    private $log, $checkLog;
    public function error(Request $request)
    {
        $user  = new AuthController();
        $me = $user->me()->getData();
        $validator = Validator::make($request->all(), [
            'error_code' => 'required',
            'log_id' => 'required|exists:log_user_login,log_id'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->checkLog = DB::table('log_error')
                ->insert([
                    'error_code' => $request->error_code,
                    'error_date' => DB::raw('sysdate()'),
                    'log_user_login_id' => $request->log_id,
                    'log_note' => $request->log_note
                ]);

            if ($this->checkLog == 0) {
                return response()->json(['status' => 'error', 'message' => 'Failed to insert log error']);
            } else {
                return response()->json(['status' => 'ok', 'message' => 'Log error has been inserted']);
            }
        }
    }

    public function menu(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'log_id' => 'required|exists:log_user_login,log_id',
            'menu_id' => 'required|exists:menu,menu_id'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->checkLog = DB::table('log_menu')
                ->insert([
                    'log_user_login_id' => $request->log_id,
                    'menu_id' => $request->menu_id,
                    'access_on' => DB::raw('sysdate()'),
                ]);

            if ($this->checkLog == 0) {
                return response()->json(['status' => 'error', 'message' => 'Failed to insert log menu']);
            } else {
                return response()->json(['status' => 'ok', 'message' => 'Log menu has been inserted']);
            }
        }
    }

    public function menuAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'log_id' => 'required|exists:log_user_login,log_id',
            'menu_id' => 'required|exists:menu,menu_id',
            'menu_action_type_id' => 'required|exists:menu_action_type,menu_action_type_id'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->checkLog = DB::table('log_menu_action')
                ->insert([
                    'log_user_login_id' => $request->log_id,
                    'menu_id' => $request->menu_id,
                    'menu_action_type_id' => $request->menu_action_type_id,
                    'action_on' => DB::raw("sysdate()")
                ]);
            if ($this->checkLog == 0) {
                return response()->json(['status' => 'error', 'message' => 'Failed to insert log menu action']);
            } else {
                return response()->json(['status' => 'ok', 'message' => 'Log menu action has been inserted']);
            }
        }
    }
}
