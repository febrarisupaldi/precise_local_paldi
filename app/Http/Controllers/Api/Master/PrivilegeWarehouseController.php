<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PrivilegeWarehouseController extends Controller
{
    private $privilegeWH, $checkPrivilegeWH;
    public function index()
    {
        $this->privilegeWH = DB::table('privilege_warehouse as a')
            ->select(
                'privilege_warehouse_id',
                'user_id as User ID',
                'employee_name as Nama user',
                'warehouse_code as Kode gudang',
                'warehouse_name as Nama gudang',
                'privilege_type as Tipe privilege',
                'a.created_on as Tanggal input',
                'a.created_by as User input',
                'a.updated_on as Tanggal update',
                'a.updated_by as User update'
            )
            ->get();
        return response()->json(["data" => $this->privilegeWH]);
    }

    public function show($id)
    {
        $this->privilegeWH = DB::table('privilege_warehouse as a')
            ->where('privilege_warehouse_id', $id)
            ->select(
                'a.user_id',
                'e.employee_name',
                'a.warehouse_id',
                'w.warehouse_name',
                'a.privilege_type'
            )
            ->leftJoin('precise.employee e', 'a.user_id', '=', 'e.employee_nik')
            ->leftJoin('precise.warehouse w', 'a.warehouse_id', '=', 'w.warehouse_id')
            ->get();
        return response()->json(["data" => $this->privilegeWH]);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,user_id',
            'warehouse_id' => 'required|exists:warehouse,warehouse_id',
            'privilege_type' => ['required', Rule::in(['IN', 'OUT'])],
            'created_by' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->checkPrivilegeWH = DB::table('privilege_warehouse')
                ->insert([
                    'user_id' => $request->user_id,
                    'warehouse_id' => $request->warehouse_id,
                    'privilege_type' => $request->privilege_type,
                    'created_by' => $request->created_by
                ]);

            if ($this->checkPrivilegeWH == 0) {
                return response()->json(['status' => 'error', 'message' => 'Failed to add privilege warehouse']);
            } else {
                return response()->json(['status' => 'ok', 'message' => 'privilege warehouse has been added']);
            }
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'privilege_warehouse_id' => 'required',
            'user_id' => 'required|exists:users,user_id',
            'warehouse_id' => 'required|exists:warehouse,warehouse_id',
            'privilege_type' => ['required', Rule::in(['IN', 'OUT'])],
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
                $this->checkPrivilegeWH = DB::table('privilege_warehouse')
                    ->where('privilege_warehouse_id', $request->priv_wh_id)
                    ->update([
                        'user_id' => $request->user_id,
                        'warehouse_id' => $request->warehouse_id,
                        'privilege_type' => $request->privilege_type,
                        'updated_by' => $request->created_by
                    ]);
                if ($this->checkPrivilegeWH == 0) {
                    DB::rollBack();
                    return response()->json(['status' => 'error', 'message' => 'Failed to update privilege warehouse']);
                } else {
                    DB::commit();
                    return response()->json(['status' => 'ok', 'message' => 'privilege warehouse has been updated']);
                }
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $helper = new HelperController();
            $helper->reason("update");
            $this->checkPrivilegeWH = DB::table('privilege_warehouse')
                ->where('privilege_wh_id', $id)
                ->delete();

            if ($this->checkPrivilegeWH == 0) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Failed to delete privilege warehouse']);
            } else {
                DB::commit();
                return response()->json(['status' => 'ok', 'message' => 'privilege warehouse has been deleted']);
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
            'warehouse_id' => 'required',
            'privilege_type' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->checkPrivilegeWH = DB::table('privilege_warehouse')->where([
                'user_id' => $request->get('user_id'),
                'warehouse_id' => $request->get('warehouse_id'),
                'privilege_type' => $request->get('privilege_type')
            ])->count();
        }
        return response()->json(["status" => "ok", "message" => $this->checkPrivilegeWH]);
    }
}
