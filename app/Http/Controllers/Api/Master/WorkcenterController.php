<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WorkcenterController extends Controller
{
    private $workcenter, $checkWorkcenter;
    public function index()
    {
        $this->workcenter = DB::table('workcenter as wc')
            ->select(
                'workcenter_id',
                'workcenter_code as Kode workcenter',
                'workcenter_name as Nama workcenter',
                'workcenter_description as Deskripsi',
                DB::raw("concat(wh.warehouse_code, ' - ', wh.warehouse_name) 'Gudang default'
            , case wc.is_active 
                when 0 then 'Tidak aktif'
                when 1 then 'Aktif' 
            end as 'Status aktif'"),
                'wh.created_on as Tanggal input',
                'wh.created_by as User input',
                'wh.updated_on as Tanggal update',
                'wh.updated_by as User update'
            )->leftJoin('warehouse as wh', 'wc.default_warehouse', '=', 'wh.warehouse_id')
            ->get();
        return response()->json(['data' => $this->workcenter]);
    }

    public function show($id)
    {
        $this->workcenter = DB::table('workcenter')
            ->select(
                'workcenter_id',
                'workcenter_code',
                'workcenter_name',
                'workcenter_description',
                'warehouse.warehouse_id',
                'warehouse.warehouse_code',
                'warehouse.warehouse_name',
                'workcenter.is_active'
            )->leftJoin('warehouse', 'workcenter.default_warehouse', '=', 'warehouse.warehouse_id')
            ->where('workcenter_id', $id)
            ->get();

        return response()->json(['data' => $this->workcenter]);
    }

    public function showByCode($id)
    {
        $this->workcenter = DB::table('precise.workcenter as wc')
            ->select(
                'wc.workcenter_id',
                'wc.workcenter_code',
                'wc.workcenter_name',
                'wc.workcenter_description',
                'wh.warehouse_id',
                'wh.warehouse_code',
                'wh.warehouse_name',
                'wc.is_active'
            )->leftJoin('precise.warehouse as wh', 'wc.default_warehouse', '=', 'wh.warehouse_id')
            ->where('wc.workcenter_code', $id)
            ->get();

        return response()->json(['data' => $this->workcenter]);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'workcenter_code' => 'required|unique:workcenter',
            'workcenter_name' => 'required',
            'created_by'      => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->checkWorkcenter = DB::table('workcenter')->insert([
                'workcenter_code' => $request->workcenter_code,
                'workcenter_name' => $request->workcenter_name,
                'workcenter_description' => $request->desc,
                'default_warehouse' => $request->warehouse_id,
                'created_by' => $request->created_by
            ]);

            if ($this->checkWorkcenter == 0) {
                return response()->json(['status' => 'error', 'message' => 'Failed to create workcenter, Contact your administrator']);
            } else {
                return response()->json(['status' => 'ok', 'message' => $request->workcenter_name . ' has been created']);
            }
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'workcenter_code' => 'required',
            'workcenter_name' => 'required',
            'warehouse_id'       => 'required',
            'is_active'       => 'required|boolean',
            'updated_by'      => 'required',
            'reason' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            DB::beginTransaction();
            try {
                $helper = new HelperController();
                $helper->reason("update");
                $this->checkWorkcenter = DB::table('workcenter')
                    ->where('workcenter_id', $request->workcenter_id)
                    ->update(
                        [
                            'workcenter_code' => $request->workcenter_code,
                            'workcenter_name' => $request->workcenter_name,
                            'workcenter_description' => $request->desc,
                            'default_warehouse' => $request->warehouse_id,
                            'is_active' => $request->is_active,
                            'updated_by' => $request->updated_by
                        ]
                    );

                if ($this->checkWorkcenter == 0) {
                    DB::rollback();
                    return response()->json(['status' => 'error', 'message' => 'Failed to update workcenter, Contact your administrator']);
                } else {
                    DB::commit();
                    return response()->json(['status' => 'ok', 'message' => $request->workcenter_name . ' has been update']);
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
            $helper->reason("delete");
            $this->checkWorkcenter = DB::table('workcenter')
                ->where('workcenter_id', $id)
                ->delete();

            if ($this->checkWorkcenter == 0) {
                DB::rollback();
                return response()->json(['status' => 'error', 'message' => 'Failed to delete workcenter, Contact your administrator']);
            } else {
                DB::commit();
                return response()->json(['status' => 'ok', 'message' => 'Workcenter has been deleted']);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
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
            if ($type == "code") {
                $this->checkWorkcenter = DB::table('workcenter')
                    ->where('workcenter_code', $value)
                    ->count();
            } elseif ($type == "name") {
                $this->checkWorkcenter = DB::table('workcenter')
                    ->where('workcenter_name', $value)
                    ->count();
            }
            return response()->json(['status' => 'ok', 'message' => $this->checkWorkcenter]);
        }
    }
}
