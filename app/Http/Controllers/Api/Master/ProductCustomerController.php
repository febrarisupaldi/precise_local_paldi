<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ProductCustomerController extends Controller
{
    private $productCustomer, $checkProductCustomer;
    public function index()
    {
        $this->productCustomer = DB::table('product_customer as a')
            ->select(
                'a.product_customer_id',
                'p.product_code as Kode barang',
                'p.product_name as Nama barang',
                'c.customer_code as Kode customer',
                'c.customer_name as Nama customer',
		'a.loss_tolerance as Loss tolerance',
		'a.moq',
		'a.oem_material_supply_type',
		DB::raw("case a.is_return_runner 
                    when 0 then 'Tidak'
                    when 1 then 'Ya' 
                end as 'is_return_runner'"),
		DB::raw("case a.is_order_qty_include_reject 
                    when 0 then 'Tidak'
                    when 1 then 'Ya' 
                end as 'is_order_qty_include_reject'"),
                DB::raw("case a.is_active 
                    when 0 then 'Tidak aktif'
                    when 1 then 'Aktif' 
                end as 'Status aktif order'"),
                'a.created_on as Tanggal input',
                'a.created_by as User input',
                'a.updated_on as Tanggal update',
                'a.updated_by as User update'
            )->leftJoin('product as p', 'a.product_id', '=', 'p.product_id')
            ->leftJoin('customer as c', 'a.customer_id', '=', 'c.customer_id')
            ->get();

        return response()->json(["data" => $this->productCustomer]);
    }

    public function show($id)
    {
	$this->productCustomer = DB::table('product_customer as a')
            ->where('product_customer_id', $id)
            ->select(
		'a.product_customer_id',
                'a.product_id',
                'p.product_code',
                'p.product_name',
                'a.customer_id',
                'c.customer_code',
                'c.customer_name',
		'a.loss_tolerance',
		'a.moq',
		'a.oem_material_supply_type',
		'a.is_return_runner', 
		'a.is_order_qty_include_reject', 
                'a.is_active',
		'a.created_on', 
		'a.created_by', 
		'a.updated_on', 
		'a.updated_by'
            )
            ->leftJoin('precise.product as p', 'a.product_id', '=', 'p.product_id')
            ->leftJoin('precise.customer as c', 'a.customer_id', '=', 'c.customer_id')
            ->first();

        return response()->json($this->productCustomer);
    }

    public function showCustomer($id)
    {
        $this->productCustomer = DB::table('product_customer as a')
            ->select(
		'a.product_customer_id', 
                'a.product_id',
                'p.product_code as Kode barang',
                'p.product_name as Nama barang',
		'a.loss_tolerance as Loss tolerance',
		'a.moq',
		'a.oem_material_supply_type',
		DB::raw("case a.is_return_runner 
                    when 0 then 'Tidak'
                    when 1 then 'Ya' 
                end as 'is_return_runner'"),
		DB::raw("case a.is_order_qty_include_reject 
                    when 0 then 'Tidak'
                    when 1 then 'Ya' 
                end as 'is_order_qty_include_reject'"),
                DB::raw("case a.is_active 
                when 0 then 'Tidak aktif'
                when 1 then 'Aktif' 
            end as 'Status aktif order'")
            )->leftJoin('precise.product as p', 'a.product_id', '=', 'p.product_id')
            ->leftJoin('precise.customer as c', 'a.customer_id', '=', 'c.customer_id')
            ->where([
                'a.customer_id' => $id,
                'a.is_active' => 1
            ])->get();

        return response()->json(["data" => $this->productCustomer]);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:product,product_id',
            'customer_id' => 'required|exists:customer,customer_id',
            'created_by' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->checkProductCustomer = DB::table('product_customer')->insert([
                'product_id' => $request->product_id,
                'customer_id' => $request->customer_id,
                'loss_tolerance' => $request->loss_tolerance,
                'moq' => $request->moq,
                'oem_material_supply_type' => $request->material_supply_type,
                'is_return_runner' => $request->is_return_runner,
                'is_order_qty_include_reject' => $request->is_order_qty_include_reject,
                'created_by' => $request->created_by
            ]);

            if ($this->checkProductCustomer == 0) {
                return response()->json(['status' => 'error', 'message' => 'Failed to insert new product customer']);
            } else {
                return response()->json(['status' => 'ok', 'message' => 'product customer has been inserted']);
            }
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_customer_id' => 'required',
            'product_id' => 'required|exists:product,product_id',
            'customer_id' => 'required|exists:customer,customer_id',
            'is_active' => 'required|boolean',
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
                $this->checkProductCustomer = DB::table('product_customer')
                    ->where('product_customer_id', $request->product_customer_id)
                    ->update([
                        'product_id' => $request->product_id,
                        'customer_id' => $request->customer_id,
			'loss_tolerance' => $request->loss_tolerance,
			'moq' => $request->moq,
			'oem_material_supply_type' => $request->material_supply_type,
			'is_return_runner' => $request->is_return_runner,
			'is_order_qty_include_reject' => $request->is_order_qty_include_reject,
                        'is_active' => $request->is_active,
                        'updated_by' => $request->created_by
                    ]);

                if ($this->checkProductCustomer == 0) {
                    DB::rollBack();
                    return response()->json(['status' => 'error', 'message' => 'Failed to update new product customer']);
                } else {
                    DB::commit();
                    return response()->json(['status' => 'ok', 'message' => 'product customer has been updated']);
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
            $this->checkProductCustomer = DB::table('product_customer')
                ->where('product_customer_id', $id)
                ->delete();

            if ($this->checkProductCustomer == 0) {
                DB::rollback();
                return response()->json(['status' => 'error', 'message' => 'Failed to delete product customer, Contact your administrator']);
            } else {
                DB::commit();
                return response()->json(['status' => 'ok', 'message' => 'Product customer has been deleted']);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function check(Request $request)
    {
        $product = $request->get('product_id');
        $customer = $request->get('customer_id');

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:product,product_id',
            'customer_id' => 'required|exists:customer,customer_id'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->checkProductCustomer = DB::table('product_customer')->where([
                'product_id' => $product,
                'customer_id' => $customer
            ])->count();

            return response()->json(['status' => 'ok', 'message' => $this->checkProductCustomer]);
        }
    }
}
