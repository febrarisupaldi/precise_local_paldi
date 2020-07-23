<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ProductAppearanceController extends Controller
{
    private $productAppearance, $checkProductAppearance;
    public function index()
    {
        $this->productAppearance = DB::table('product_appearance')->select(
            'appearance_id',
            'appearance_name as Nama tampilan',
            'created_on as Tanggal input',
            'created_by as User input',
            'updated_on as Tanggal update',
            'updated_by as User update'
        )->get();
        return response()->json(['data' => $this->productAppearance]);
    }

    public function show($id)
    {
        $this->productAppearance = DB::table('product_appearance')->where('appearance_id', $id)->select('appearance_id', 'appearance_name')->get();
        return response()->json(['data' => $this->productAppearance]);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'appearance_name' => 'required',
            'created_by' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->checkProductAppearance = DB::table('product_appearance')
                ->insert([
                    'appearance_name' => $request->appearance_name,
                    'created_by' => $request->created_by
                ]);

            if ($this->checkProductAppearance == 0) {
                return response()->json(['status' => 'error', 'message' => 'Failed to insert Product Appearance']);
            } else {
                return response()->json(['status' => 'ok', 'message' => $request->appearance_name . ' has been inserted']);
            }
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'appearance_id' => 'required',
            'appearance_name' => 'required',
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
                $this->checkProductAppearance = DB::table('product_appearance')
                    ->where('appearance_name', $request->appearance_id)
                    ->update([
                        'appearance_name' => $request->appearance_name,
                        'updated_by' => $request->updated_by
                    ]);

                if ($this->checkProductAppearance == 0) {
                    DB::rollBack();
                    return response()->json(['status' => 'error', 'message' => 'Failed to update Product Appearance']);
                } else {
                    DB::commit();
                    return response()->json(['status' => 'ok', 'message' => $request->appearance_name . ' has been inserted']);
                }
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
    }
}
