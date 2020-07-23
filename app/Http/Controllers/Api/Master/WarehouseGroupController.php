<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WarehouseGroupController extends Controller
{
    private $warehouseGroup, $checkWarehouseGroup;
    public function index()
    {
        $this->warehouseGroup = DB::table('warehouse_group')->select(
            'warehouse_group_code as Kode Group',
            'warehouse_group_name as Nama Group',
            'created_on as Tanggal Input',
            'created_by as User Input',
            'updated_on as Tanggal Update',
            'updated_by as User Update'
        )->get();
        return response()->json(['data' => $this->warehouseGroup]);
    }

    public function show($id)
    {
        $this->warehouseGroup = DB::table('warehouse_group')
            ->where('warehouse_group_code', $id)
            ->get();
        return response()->json(['data' => $this->warehouseGroup]);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'warehouse_group_code' => 'required|unique:warehouse_group',
            'warehouse_group_name' => 'required',
            'created_by' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {

            $this->checkWarehouseGroup = DB::table('warehouse_group')
                ->insert([
                    'warehouse_group_code' => $request->warehouse_group_code,
                    'warehouse_group_name' => $request->warehouse_group_name,
                    'created_by' => $request->created_by
                ]);

            if ($this->checkWarehouseGroup == 0) {
                return response()->json(['status' => 'error', 'message' => 'Failed insert ' . $request->warehouse_group_name . ' , contact your administrator']);
            } else {
                return response()->json(['status' => 'ok', 'message' => $request->warehouse_group_name . ' was inserted']);
            }
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'warehouse_group_code' => 'required',
            'new_warehouse_group_code' => 'required',
            'warehouse_group_name' => 'required',
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
                $this->checkWarehouseGroup = DB::table('warehouse_group')
                    ->where('warehouse_group_code', $request->warehouse_group_code)
                    ->update([
                        'warehouse_group_code' => $request->new_warehouse_group_code,
                        'warehouse_group_name' => $request->warehouse_group_name,
                        'updated_by' => $request->updated_by
                    ]);

                if ($this->checkWarehouseGroup == 0) {
                    DB::rollback();
                    return response()->json(['status' => 'error', 'message' => 'Failed to update Warehouse Group , contact your administrator']);
                } else {
                    DB::commit();
                    return response()->json(['status' => 'ok', 'message' => 'Warehouse Group has been updated']);
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
            $this->checkWarehouseGroup = DB::table('warehouse_group')
                ->where('warehouse_group_code', $id)
                ->delete();
            if ($this->checkWarehouseGroup == 0) {
                DB::rollback();
                return response()->json(['status' => 'error', 'message' => 'Failed to delete warehouse group, Contact your administrator']);
            } else {
                DB::commit();
                return response()->json(['status' => 'ok', 'message' => 'Warehouse Group has been deleted']);
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
                $this->checkWarehouseGroup = DB::table('warehouse_group')
                    ->where('warehouse_group_code', $value)
                    ->count();
                return response()->json(['status' => 'ok', 'message' => $this->checkWarehouseGroup]);
            }
        }
    }
}
