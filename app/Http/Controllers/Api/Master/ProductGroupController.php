<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ProductGroupController extends Controller
{
    private $productGroup, $checkProductGroup;
    public function index()
    {
        $this->productGroup = DB::table('product_group as a')
            ->select(
                'a.product_group_id',
                'product_group_code as Kode group produk',
                'product_group_name as Nama group produk',
                DB::raw("concat(b.product_type_code, ' - ', b.product_type_name) as 'Tipe produk'"),
                'a.created_on as Tanggal input',
                'a.created_by as User input',
                'a.updated_on as Tanggal edit',
                'a.updated_by as User edit'
            )->leftJoin('precise.product_type as b', 'a.product_type_id', '=', 'b.product_type_id')
            ->orderBy('product_group_code')
            ->get();

        return response()->json(["data" => $this->productGroup]);
    }

    public function show($id)
    {
        $this->productGroup = DB::table('product_group')
            ->where('product_group_id', $id)
            ->select('product_group_code', 'product_group_Name', 'product_type_id')
            ->get();

        return response()->json(["data" => $this->productGroup]);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_group_code' => 'required|unique:product_group',
            'product_group_name' => 'required',
            'product_type_id' => 'required|exists:product_type,product_type_id',
            'created_by' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->checkProductGroup = DB::table('product_group')
                ->insert([
                    'product_group_code' => $request->product_group_code,
                    'product_group_name' => $request->product_group_name,
                    'product_type_id' => $request->product_type_id,
                    'created_by' => $request->created_by
                ]);

            if ($this->checkProductGroup == 0) {
                return response()->json(['status' => 'error', 'message' => 'Failed to insert new product group']);
            } else {
                return response()->json(['status' => 'ok', 'message' => 'product group has been inserted']);
            }
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_group_id' => 'required',
            'product_group_code' => 'required',
            'product_group_name' => 'required',
            'product_type_id' => 'required|exists:product_type,product_type_id',
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
                $this->checkProductGroup = DB::table('product_group')
                    ->where('product_group_id', $request->product_group_id)
                    ->update([
                        'product_group_code' => $request->product_group_code,
                        'product_group_name' => $request->product_group_name,
                        'product_type_id' => $request->product_type_id,
                        'updated_by' => $request->updated_by
                    ]);

                if ($this->checkProductGroup == 0) {
                    DB::rollBack();
                    return response()->json(['status' => 'error', 'message' => 'Failed to update product group']);
                } else {
                    DB::commit();
                    return response()->json(['status' => 'ok', 'message' => 'product group has been updated']);
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
                $this->checkProductGroup = DB::table('product_group')
                    ->where([
                        'product_group_code' => $value
                    ])->count();
            } else if ($type == "name") {
                $this->checkProductGroup = DB::table('product_group')
                    ->where([
                        'product_group_name' => $value
                    ])->count();
            }
            return response()->json(['status' => 'ok', 'message' => $this->checkProductGroup]);
        }
    }
}
