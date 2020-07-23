<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UOMController extends Controller
{
    private $uom, $checkUOM;
    public function index()
    {
        $this->uom = DB::table('uom')
            ->select(
                'uom_code as Kode UOM',
                'uom_name as Nama UOM',
                DB::raw("if(is_active = 1, 'Aktif', 'Tidak aktif') 'Status aktif'"),
                'created_on as Tanggal input',
                'created_by as User input',
                'updated_on as Tanggal edit',
                'updated_by as User edit'
            )
            ->get();
        return response()->json(['data' => $this->uom]);
    }

    public function show($id)
    {
        $this->uom = DB::table('uom')->select('uom_code', 'uom_name', 'is_active')->where('uom_code', $id)->get();
        return response()->json(['data' => $this->uom]);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uom_code' => 'required|unique:uom, uom_code',
            'uom_name' => 'required',
            'created_by' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->checkUOM = DB::table('uom')->insert([
                'uom_code' => $request->uom_code,
                'uom_name' => $request->uom_name,
                'created_by' => $request->created_by
            ]);

            if ($this->checkUOM == 0) {
                return response()->json(['status' => 'error', 'message' => 'Failed to create UOM, Contact your administrator']);
            } else {
                return response()->json(['status' => 'ok', 'message' => $request->uom_name . ' has been created']);
            }
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uom_code' => 'required',
            'uom_name' => 'required',
            'is_active' => 'required',
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
                $this->checkUOM = DB::table('uom')->where('uom_code', $request->uom_code)->update([
                    'uom_name' => $request->uom_name,
                    'is_active' => $request->is_active,
                    'updated_by' => $request->updated_by
                ]);

                if ($this->checkUOM == 0) {
                    DB::rollback();
                    return response()->json(['status' => 'error', 'message' => 'Failed to update UOM, Contact your administrator']);
                } else {
                    DB::commit();
                    return response()->json(['status' => 'ok', 'message' => 'UOM has been update']);
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
            if ($type == "code") {
                $uom = DB::table('uom')->where('uom_code', $value)->count();
            } elseif ($type == "name") {
                $uom = DB::table('uom')->where('uom_name', $value)->count();
            }
            return response()->json(['status' => 'ok', 'message' => $uom]);
        }
    }
}
