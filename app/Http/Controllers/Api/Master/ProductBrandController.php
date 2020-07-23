<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ProductBrandController extends Controller
{
    private $productBrand, $checkProductBrand;
    public function index()
    {
        $this->productBrand = DB::table('product_brand')->select(
            'product_brand_id',
            'product_brand_name as Nama brand',
            'created_on as Tanggal input',
            'created_by as User input',
            'updated_on as Tanggal update',
            'updated_by as User update'
        )->get();
        return response()->json(['data' => $this->productBrand]);
    }

    public function show($id)
    {
        $this->productBrand = DB::table('product_brand')
            ->where('product_brand_id', $id)
            ->select('product_brand_name')
            ->get();
        return response()->json(['data' => $this->productBrand]);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_brand_name' => 'required',
            'created_by' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->checkProductBrand = DB::table('product_brand')
                ->insert([
                    'product_brand_name' => $request->product_brand_name,
                    'created_by' => $request->created_by
                ]);

            if ($this->checkProductBrand == 0) {
                return response()->json(['status' => 'error', 'message' => 'Failed to insert Product Brand']);
            } else {
                return response()->json(['status' => 'ok', 'message' => $request->product_brand_name . ' has been inserted']);
            }
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_brand_id'   => 'required|exists:product_brand,product_brand_id',
            'product_brand_name' => 'required',
            'is_active'  => 'required',
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
                $this->checkProductBrand = DB::table('product_brand')
                    ->where('product_brand_id', $request->product_brand_id)
                    ->update([
                        'is_active' => $request->is_active,
                        'product_brand_name' => $request->product_brand_name,
                        'updated_by' => $request->updated_by
                    ]);

                if ($this->checkProductBrand == 0) {
                    DB::rollback();
                    return response()->json(['status' => 'error', 'message' => 'Failed to update Product Brand']);
                } else {
                    DB::commit();
                    return response()->json(['status' => 'ok', 'message' => $request->product_brand_name . ' has been updated']);
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
            if ($type == "name") {
                $this->checkProductBrand = DB::table('product_brand')->where('product_brand_name', $value)->count();
            }
            return response()->json(['status' => 'ok', 'message' => $this->checkProductBrand]);
        }
    }
}
