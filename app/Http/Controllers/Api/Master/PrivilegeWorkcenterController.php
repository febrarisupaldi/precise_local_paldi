<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PrivilegeWorkcenterController extends Controller
{
    private $privilegeWC, $checkPrivilegeWC;

    public function index()
    {
        $this->privilegeWC = DB::table('privilege_workcenter as a')
            ->select(
                'privilege_workcenter_id',
                'a.user_id as User ID',
                'e.employee_name as Nama user',
                'w.workcenter_code as Kode workcenter',
                'w.workcenter_name as Nama workcenter',
                'a.created_on as Tanggal input',
                'a.created_by as User input',
                'a.updated_on as Tanggal update',
                'a.updated_by as User update'
            )
            ->leftJoin('employee as e', 'a.user_id', '=', 'e.employee_nik')
            ->leftJoin('workcenter as w', 'a.workcenter_id', '=', 'w.workcenter_id')
            ->get();
        return response()->json(["data" => $this->privilegeWC]);
    }

    public function show($id)
    {
        $this->privilegeWC = DB::table('privilege_workcenter as a')
            ->where('privilege_workcenter_id', $id)
            ->select(
                'privilege_workcenter_id',
                'a.user_id',
                'e.employee_name',
                'a.workcenter_id',
                'w.workcenter_code',
                'w.workcenter_name'
            )
            ->leftJoin('employee as e', 'a.user_id', '=', 'e.employee_nik')
            ->leftJoin('workcenter as w', 'a.workcenter_id', '=', 'w.workcenter_id')
            ->get();
        return response()->json(["data" => $this->privilegeWC]);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,user_id',
            'workcenter_id' => 'required|exists:workcenter,workcenter_id',
            'created_by' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->checkPrivilegeWC = DB::table('privilege_workcenter')
                ->insert([
                    'user_id' => $request->user_id,
                    'workcenter_id' => $request->workcenter_id,
                    'created_by' => $request->created_by
                ]);

            if ($this->checkPrivilegeWC == 0) {
                return response()->json(['status' => 'error', 'message' => 'Failed to add privilege workcenter']);
            } else {
                return response()->json(['status' => 'ok', 'message' => 'privilege workcenter has been added']);
            }
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'privilege_workcenter_id' => 'required|exists:privilege_workcenter,privilege_workcenter_id',
            'user_id' => 'required|exists:users,user_id',
            'workcenter_id' => 'required|exists:workcenter,workcenter_id',
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
                $this->checkPrivilegeWC = DB::table('privilege_workcenter')
                    ->where('privilege_workcenter_id', $request->privilege_workcenter_id)
                    ->update([
                        'user_id' => $request->user_id,
                        'workcenter_id' => $request->workcenter_id,
                        'updated_by' => $request->updated_by
                    ]);

                if ($this->checkPrivilegeWC == 0) {
                    DB::rollBack();
                    return response()->json(['status' => 'error', 'message' => 'Failed to update privilege workcenter']);
                } else {
                    DB::commit();
                    return response()->json(['status' => 'ok', 'message' => 'privilege workcenter has been updated']);
                }
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
    }

    public function destroy($id)
    {
        try {
            $helper = new HelperController();
            $helper->reason("delete");

            $this->checkPrivilegeWC = DB::table('privilege_workcenter')
                ->where('privilege_workcenter_id', $id)
                ->delete();

            if ($this->checkPrivilegeWC == 0) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Failed to delete privilege workcenter']);
            } else {
                DB::commit();
                return response()->json(['status' => 'ok', 'message' => 'privilege workcenter has been deleted']);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function check(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'workcenter_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->checkPrivilegeWC = DB::table('privilege_workcenter')
                ->where([
                    ['user_id', '=', $request->get('user_id')],
                    ['workcenter_id', '=', $request->get('workcenter_id')]
                ])
                ->count();
            return response()->json(["status" => "ok", "message" => $this->checkPrivilegeWC]);
        }
    }
}
