<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\Helpers\QueryController;

class ProductItemController extends Controller
{
    private $productItem, $checkProductItem;

    public function index($id)
    {
        $id = explode("-", $id);
        $this->productItem = DB::table('product_item as a')
            ->whereIn('kind_code', $id)
            ->select(
                'item_id',
                'item_code as Kode item',
                'item_name as Nama item',
                'item_alias as Nama alias',
                DB::raw("concat(product_kind_code,'-', product_kind_name) as `Jenis item`"),
                'c.series_name as Nama seri produk',
                DB::raw("case `a`.`is_active_sell`
                    when 0 then 'Tidak aktif'
                    when 1 then 'Aktif'
                end as 'Status aktif jual'"),
                DB::raw("case `a`.`is_active_production`
                    when 0 then 'Tidak aktif'
                    when 1 then 'Aktif'
                end as 'Status aktif produksi'"),
                'a.created_on as Tanggal input',
                'a.created_by as User input',
                'a.updated_on as Tanggal update',
                'a.updated_by as User update'
            )
            ->leftJoin('product_kind as b', 'a.kind_code', '=', 'b.product_kind_code')
            ->leftJoin('product_series as c', 'a.series_id', '=', 'c.series_id')
            ->get();
        return response()->json(["data" => $this->productItem]);
    }

    public function show($id)
    {
        $this->productItem = DB::table('product_item')
            ->where('item_id', $id)
            ->select(
                'item_id',
                'item_code',
                'item_name',
                'item_alias',
                'kind_code',
                'series_id',
                'is_active_sell',
                'is_active_production'
            )
            ->first();
        return response()->json($this->productItem);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_code' => 'required|unique:product_item,item_code',
            'item_name' => 'required',
            'kind_code' => 'required|exists:product_kind,product_kind_code',
            'series_id' => 'required|exists:product_series,series_id',
            'created_by' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->checkProductItem = DB::table('product_item')
                ->insert([
                    'item_code' => $request->item_code,
                    'item_name' => $request->item_name,
                    'item_alias' => $request->item_alias,
                    'kind_code' => $request->kind_code,
                    'series_id' => $request->series_id,
                    'created_by' => $request->created_by
                ]);

            if ($this->checkProductItem == 0) {
                return response()->json(['status' => 'error', 'message' => 'Failed to insert new product item']);
            } else {
                return response()->json(['status' => 'ok', 'message' => 'product item has been inserted']);
            }
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required',
            'item_code' => 'required',
            'item_name' => 'required',
            'kind_code' => 'required|exists:product_kind,product_kind_code',
            'series_id' => 'required|exists:product_series,series_id',
            'is_active_sell' => 'required|boolean',
            'is_active_production' => 'required|boolean',
            'updated_by' => 'required',
            'reason' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            try {
                DB::beginTransaction();
                QueryController::reasonAction("update");
                $this->checkProductItem = DB::table('product_item')
                    ->where('item_id', $request->item_id)
                    ->update([
                        'item_code' => $request->item_code,
                        'item_name' => $request->item_name,
                        'item_alias' => $request->item_alias,
                        'kind_code' => $request->kind_code,
                        'series_id' => $request->series_id,
                        'is_active_sell' => $request->is_active_sell,
                        'is_active_production' => $request->is_active_production,
                        'updated_by' => $request->updated_by
                    ]);
                if ($this->checkProductItem == 0) {
                    DB::rollback();
                    return response()->json(['status' => 'error', 'message' => 'Failed to update new product item']);
                } else {
                    DB::commit();
                    return response()->json(['status' => 'ok', 'message' => 'product item has been updated']);
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
            $this->checkProductItem = DB::table('product_item')
                ->where('item_id', $id)->delete();

            if ($this->checkProductItem == 0) {
                DB::rollback();
                return response()->json(['status' => 'error', 'message' => 'Failed to delete product item, Contact your administrator']);
            } else {
                DB::commit();
                return response()->json(['status' => 'ok', 'message' => 'Product item has been deleted']);
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
                $this->checkProductItem = DB::table('product_item')->where([
                    'item_code' => $value
                ])->count();
            } else if ($type == "alias") {
                $this->checkProductItem = DB::table('product_item')->where([
                    'item_alias' => $value
                ])->count();
            }
            return response()->json(['status' => 'ok', 'message' => $this->checkProductItem]);
        }
    }
}
