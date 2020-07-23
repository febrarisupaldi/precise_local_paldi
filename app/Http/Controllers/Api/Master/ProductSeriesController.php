<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\Master\HelperController;

class ProductSeriesController extends Controller
{
    private $productSeries, $checkProductSeries;
    public function index()
    {
        $this->productSeries = DB::table('product_series')
            ->select(
                'series_id',
                'series_name as Nama seri',
                'series_description as Deskripsi seri',
                'created_on as Tanggal input',
                'created_by as User input',
                'updated_on as Tanggal update',
                'updated_by as User update'
            )->get();

        return response()->json(["data" => $this->productSeries]);
    }

    public function show($id)
    {
        $this->productSeries = DB::table('product_series')
            ->where('series_id', $id)
            ->select(
                'series_id',
                'series_name',
                'series_description'
            )
            ->get();
        return response()->json(["data" => $this->productSeries]);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'series_name' => 'required',
            'created_by' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->checkProductSeries = DB::table('product_series')
                ->insert([
                    'series_name' => $request->series_name,
                    'series_description' => $request->desc,
                    'created_by' => $request->created_by
                ]);

            if ($this->checkProductSeries == 0) {
                return response()->json(['status' => 'error', 'message' => 'Failed to insert new product series']);
            } else {
                return response()->json(['status' => 'ok', 'message' => 'product series has been inserted']);
            }
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'series_id' => 'required',
            'series_name' => 'required',
            'updated_by' => 'required',
            'reason' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            DB::beginTransaction();
            try {
                $helper = new HelperController();
                $check = $helper->reason("update");

                $this->checkProductSeries = DB::table('product_series')
                    ->where('series_id', $request->series_id)
                    ->update([
                        'series_name' => $request->series_name,
                        'series_description' => $request->desc,
                        'updated_by' => $request->updated_by
                    ]);

                if ($this->checkProductSeries == 0) {
                    DB::rollback();
                    return response()->json(['status' => 'error', 'message' => 'Failed to update new product series']);
                } else {
                    DB::commit();
                    return response()->json(['status' => 'ok', 'message' => "Product series has been"]);
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
            $check = $helper->reason("delete");

            $this->checkProductSeries = DB::table('product_series')
                ->where('series_id', $id)->delete();

            if ($this->checkProductSeries == 0) {
                DB::rollback();
                return response()->json(['status' => 'error', 'message' => 'Failed to delete product series, Contact your administrator']);
            } else {
                DB::commit();
                return response()->json(['status' => 'ok', 'message' => 'Product series has been deleted']);
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
            if ($type == "name") {
                $this->checkProductSeries = DB::table('product_series')->where([
                    'series_name' => $value
                ])->count();
            }
            return response()->json(['status' => 'ok', 'message' => $this->checkProductSeries]);
        }
    }
}
