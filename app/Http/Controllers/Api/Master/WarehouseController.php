<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WarehouseController extends Controller
{
    private $warehouse, $checkWarehouse;
    public function show($id)
    {

        $this->warehouse = DB::table('warehouse')
            ->select(
                'warehouse_id',
                'warehouse_code',
                'warehouse_name',
                'warehouse_alias',
                'warehouse_group_code',
                'is_active'
            )
            ->where('warehouse_id', $id)->get();
        return response()->json(['data' => $this->warehouse]);
    }

    public function showByWarehouseGroup($id)
    {
        $id = explode("-", $id);
        $this->warehouse = DB::table('warehouse')
            ->select(
                'warehouse_id',
                'warehouse_code as Kode gudang',
                'warehouse_name as Nama gudang',
                'warehouse_alias as Nama alias gudang',
                'warehouse_group_code as Group gudang',
                DB::raw("case is_active 
                    when 0 then 'Tidak aktif'
                    when 1 then 'Aktif' 
                end as 'Status aktif'"),
                'created_on as Tanggal input',
                'created_by as User input',
                'updated_on as Tanggal update',
                'updated_by as User update'
            )
            ->whereIn('warehouse_group_code', $id)
            ->get();
        return response()->json(['data' => $this->warehouse]);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'warehouse_code' => 'required|unique:warehouse',
            'warehouse_name' => 'required',
            'warehouse_group_code' => 'required|exists:warehouse_group,warehouse_group_code',
            'created_by' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {


            $this->checkWarehouse = DB::table('warehouse')->insert([
                'warehouse_code' => $request->warehouse_code,
                'warehouse_name' => $request->warehouse_name,
                'warehouse_alias' => $request->warehouse_alias,
                'warehouse_group_code' => $request->warehouse_group_code,
                'created_by'      => $request->created_by
            ]);

            if ($this->checkWarehouse == 0) {
                return response()->json(['status' => 'error', 'message' => 'Failed to create warehouse, Contact your administrator']);
            } else {
                return response()->json(['status' => 'ok', 'message' => $request->warehouse_name . ' has been created']);
            }
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'warehouse_id' => 'required',
            'warehouse_code' => 'required',
            'warehouse_name' => 'required',
            'warehouse_group_code' => 'required|exists:warehouse_group,warehouse_group_code',
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
                $this->checkWarehouse = DB::table('warehouse')
                    ->where('warehouse_id', $request->warehouse_id)
                    ->update(
                        [
                            'warehouse_code' => $request->warehouse_code,
                            'warehouse_name' => $request->warehouse_name,
                            'warehouse_alias' => $request->warehouse_alias,
                            'warehouse_group_code' => $request->warehouse_group_code,
                            'is_active' => $request->is_active,
                            'updated_by' => $request->updated_by
                        ]
                    );

                if ($this->checkWarehouse == 0) {
                    DB::rollback();
                    return response()->json(['status' => 'error', 'message' => 'Failed to update warehouse, Contact your administrator']);
                } else {
                    DB::commit();
                    return response()->json(['status' => 'ok', 'message' => $request->warehouse_name . ' has been update']);
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
            $this->checkWarehouse = DB::table('warehouse')
                ->where('warehouse_id', $id)
                ->delete();

            if ($this->checkWarehouse == 0) {
                DB::rollback();
                return response()->json(['status' => 'error', 'message' => 'Failed to delete warehouse, Contact your administrator']);
            } else {
                DB::commit();
                return response()->json(['status' => 'ok', 'message' => 'Warehouse has been deleted']);
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
                $this->checkWarehouse = DB::table('warehouse')
                    ->where('warehouse_code', $value)
                    ->count();
            } elseif ($type == "name") {
                $this->checkWarehouse = DB::table('warehouse')
                    ->where('warehouse_name', $value)
                    ->count();
            } elseif ($type == "alias") {
                $this->checkWarehouse = DB::table('warehouse')
                    ->where('warehouse_alias', $value)
                    ->count();
            }
            return response()->json(['status' => 'ok', 'message' => $this->checkWarehouse]);
        }
    }
}
