<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class RetailTypeController extends Controller
{
    private $retailType, $checkRetailType;
    public function index()
    {
        $this->retailType = DB::table('retail_type')->select(
            'retail_type_id',
            'retail_type_code as Kode retail',
            'retail_type_description as Keterangan',
            'created_on as Tanggal input',
            'created_by as User input',
            'updated_on as Tanggal update',
            'updated_by as User update'
        )->get();
        return response()->json(['data' => $this->retailType]);
    }

    public function show($id)
    {
        $this->retailType = DB::table('retail_type')
            ->where('retail_type_id', $id)
            ->select('retail_type_code', 'retail_type_description')
            ->get();

        return response()->json(['data' => $this->retailType]);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'retail_type_code' => 'required|unique:retail_type',
            'desc' => 'required',
            'created_by' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->checkRetailType = DB::table('retail_type')->insert([
                'retail_type_code' => $request->retail_type_code,
                'retail_type_description' => $request->desc,
                'created_by' => $request->created_by
            ]);

            if ($this->checkRetailType == 0) {
                return response()->json(['status' => 'error', 'message' => 'Failed to insert Retail Type']);
            } else {
                return response()->json(['status' => 'ok', 'message' => $request->retail_type_code . ' has been inserted']);
            }
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'retail_type_id' => 'required',
            'retail_type_code' => 'required',
            'desc' => 'required',
            'updated_by' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            try {
                DB::beginTransaction();
                $helper = new HelperController();
                $helper->reason("update");
                $this->checkRetailType = DB::table('retail_type')
                    ->where('retail_type_id', $request->retail_type_id)
                    ->update([
                        'retail_type_code' => $request->retail_type_code,
                        'retail_type_description' => $request->desc,
                        'updated_by' => $request->updated_by
                    ]);

                if ($this->checkRetailType == 0) {
                    DB::rollback();
                    return response()->json(['status' => 'error', 'message' => 'Failed to update Retail Type']);
                } else {
                    DB::commit();
                    return response()->json(['status' => 'ok', 'message' => $request->retail_type_code . ' has been updated']);
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
                $this->checkRetailType = DB::table('retail_type')
                    ->where('retail_type_code', $value)
                    ->count();
            } elseif ($type == "desc") {
                $this->checkRetailType = DB::table('retail_type')
                    ->where('retail_type_description', $value)
                    ->count();
            }
            return response()->json(['status' => 'ok', 'message' => $this->checkRetailType]);
        }
    }
}
