<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    private $user, $checkUser;
    public function index()
    {
        $this->user = DB::table('users as u')
            ->select(
                'user_id as User ID',
                'employee_name as User name',
                'email_internal as Email internal',
                'email_external as Email external',
                'u.is_active as User Active Status',
                'e.is_active as Employee active status',
                'u.created_on as Created on',
                'u.created_by as Created by',
                'u.updated_on as Updated on',
                'u.updated_by as Updated by'
            )->leftJoin('employee as e', 'u.user_id', '=', 'e.employee_nik')
            ->get();
        return response()->json(['data' => $this->user]);
    }

    public function show($id)
    {
        $this->user = DB::table('users as u')
            ->select(
                'user_id',
                'employee_name',
                'email_internal',
                'email_external',
                'u.is_active'
            )->leftJoin('employee as e', 'u.user_id', '=', 'e.employee_nik')
            ->get();
        return response()->json(['data' => $this->user]);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|unique:users',
            'email_internal' => 'required|email',
            'email_external' => 'required|email',
            'created_by' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->checkUser = DB::table('users')->insert([
                'user_id' => $request->user_id,
                'password' => bcrypt(123456),
                'email_internal' => $request->email_internal,
                'email_external' => $request->email_external,
                'created_by' => $request->created_by
            ]);

            if ($this->checkUser == 0) {
                return response()->json(['status' => 'error', 'message' => 'Failed to insert User, Contact your administrator']);
            } else {
                return response()->json(['status' => 'ok', 'message' => 'User ' . $request->user_id . ' has been inserted']);
            }
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'email_internal' => 'required|email',
            'email_external' => 'required|email',
            'is_active' => 'required|boolean',
            'updated_by' => 'required',
            'reason' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            DB::beginTransaction();
            try {
                $helper = new HelperController();
                $helper->reason("update");
                $this->checkUser = DB::table('users')
                    ->where('user_id', $request->user_id)
                    ->update([
                        'email_internal' => $request->email_internal,
                        'email_external' => $request->email_external,
                        'is_active' => $request->is_active,
                        'updated_by' => $request->updated_by
                    ]);

                if ($this->checkUser == 0) {
                    return response()->json(['status' => 'error', 'message' => 'Failed to update User, Contact your administrator']);
                } else {
                    return response()->json(['status' => 'ok', 'message' => 'User ' . $request->user_id . ' has been updated']);
                }
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
    }

    public function check(Request $request)
    {
        $type = $request->get('type');
        $value = $request->get('value');
        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'value' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            if ($type == 'id') {
                $this->user = DB::table('users')
                    ->where('user_id', $value)
                    ->count();
            }
            return response()->json(['status' => 'ok', 'message' => $this->user]);
        }
    }

    public function resetPassword($id)
    {
        DB::beginTransaction();
        try {
            $helper = new HelperController();
            $helper->reason("update");
            $this->user = DB::table('users')
                ->where('user_id', $id)
                ->update([
                    'password' => bcrypt(123456)
                ]);

            if ($this->user == 1) {
                DB::commit();
                return response()->json(['status' => 'ok', 'message' => 'Password for user ' . $id . ' has been reset']);
            } else {
                DB::rollback();
                return response()->json(['status' => 'error', 'message' => 'Password failed to reset']);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'user_id' => 'required',
                'old_password' => 'required',
                'new_password' => 'required'
            ]

        );

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->user = DB::table('users')
                ->select('password')
                ->where('user_id', $request->user_id)
                ->first();
            if (Hash::check($request->old_password, $this->user->password)) {
                DB::beginTransaction();
                try {
                    $helper = new HelperController();
                    $helper->reason("update");
                    $this->user = DB::table('users')
                        ->where('user_id', $request->user_id)
                        ->update([
                            'password' => bcrypt($request->new_password)
                        ]);
                    if ($this->user == 1) {
                        DB::commit();
                        return response()->json(['status' => 'ok', 'message' => 'Password for user ' . $request->user_id . ' has been reset']);
                    } else {
                        DB::rollback();
                        return response()->json(['status' => 'error', 'message' => 'Password failed to reset']);
                    }
                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
                }
            } else {
                return response()->json(['status' => 'error', 'message' => 'Old password was not same']);
            }
        }
    }
}
