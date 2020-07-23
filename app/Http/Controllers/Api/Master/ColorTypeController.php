<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\Master\HelperController;

class ColorTypeController extends Controller
{
    private $colorType, $checkColorType;
    public function index()
    {
        $this->colorType = DB::table('color_type')->select(
            'color_type_id',
            'color_type_code as Kode tipe warna',
            'color_type_name as Nama tipe warna',
            'created_on as Tanggal input',
            'created_by as User input',
            'updated_on as Tanggal update',
            'updated_by as User update'
        )->get();

        return response()->json(['data' => $this->colorType]);
    }

    public function show($id)
    {
        $this->colorType = DB::table('color_type')->where('color_type_id', $id)->select(
            'color_type_id',
            'color_type_code',
            'color_type_name'
        )->get();

        return response()->json(['data' => $this->colorType]);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'color_type_code' => 'required|unique:color_type',
            'color_type_name' => 'required',
            'created_by' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->checkColorType = DB::table('color_type')->insert([
                'color_type_code' => $request->color_type_code,
                'color_type_name' => $request->color_type_name,
                'created_by' => $request->created_by
            ]);

            if ($this->checkColorType == 0) {
                return response()->json(['status' => 'error', 'message' => 'Failed to insert Color Type, Contact your administrator']);
            } else {
                return response()->json(['status' => 'ok', 'message' => $request->color_type_name . ' has been inserted']);
            }
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'color_type_id' => 'required',
            'color_type_code' => 'required',
            'color_type_name' => 'required',
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
                $this->checkColorType = DB::table('color_type')
                    ->where('color_type_id', $request->color_type_id)
                    ->update([
                        'color_type_code' => $request->color_type_code,
                        'color_type_name' => $request->color_type_name,
                        'updated_by' => $request->updated_by
                    ]);

                if ($this->checkColorType == 0) {
                    DB::rollBack();
                    return response()->json(['status' => 'error', 'message' => 'Failed to update Color Type, Contact your administrator']);
                } else {
                    DB::commit();
                    return response()->json(['status' => 'ok', 'message' => $request->color_type_name . ' has been updated']);
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
            $this->checkColorType = DB::table('color_type')->where('color_type_id', $id)->delete();

            if ($this->checkColorType == 0) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Failed to delete color type, Contact your administrator']);
            } else {
                DB::commit();
                return response()->json(['status' => 'ok', 'message' => 'Color type has been deleted']);
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
            if ($type == 'code') {
                $this->checkColorType = DB::table('color_type')
                    ->where('color_type_code', $value)
                    ->count();
            }
            return response()->json(['status' => 'ok', 'message' => $this->checkColorType]);
        }
    }
}
