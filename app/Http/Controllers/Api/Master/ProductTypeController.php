<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ProductTypeController extends Controller
{
    private $productType, $checkProductType;
    public function index()
    {
        $this->productType = DB::table('product_type')
            ->select(
                'product_type_id',
                'product_type_code',
                'product_type_name',
                'created_on',
                'created_by',
                'updated_on',
                'updated_by'
            )
            ->get();
        return response()->json(["data" => $this->productType]);
    }

    public function show($id)
    {
        $this->productType = DB::table('product_type')
            ->where('product_type_id', $id)
            ->select(
                'product_type_id',
                'product_type_code',
                'product_type_name'
            )->first();
        return response()->json($this->productType);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_type_code' => 'required|unique:product_type',
            'product_type_name' => 'required',
            'created_by' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->checkProductType = DB::table('product_type')
                ->insert([
                    'product_type_code'  => $request->product_type_code,
                    'product_type_name'  => $request->product_type_name,
                    'created_by'         => $request->created_by
                ]);

            if ($this->checkProductType == 0) {
                return response()->json(['status' => 'error', 'message' => 'Failed to insert new product type']);
            } else {
                return response()->json(['status' => 'ok', 'message' => 'product type has been inserted']);
            }
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_type_id' => 'required',
            'product_type_code' => 'required',
            'product_type_name' => 'required',
            'updated_by' => 'required',
            'reason' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            try {
                DB::beginTransaction();
                $helper = new HelperController();
                $helper->reason("update");
                $this->checkProductType = DB::table('product_type')
                    ->where('product_type_id', $request->product_type_id)
                    ->update([
                        'product_type_code' => $request->product_type_code,
                        'product_type_name' => $request->product_type_name,
                        'updated_by'        => $request->updated_by
                    ]);

                if ($this->checkProductType == 0) {
                    DB::rollback();
                    return response()->json(['status' => 'error', 'message' => 'Failed to update new product type']);
                } else {
                    DB::commit();
                    return response()->json(['status' => 'ok', 'message' => 'product type has been updated']);
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
                $this->checkProductType = DB::table('product_type')->where([
                    'product_type_code' => $value
                ])->count();
            }
            return response()->json(['status' => 'ok', 'message' => $this->checkProductType]);
        }
    }
}
