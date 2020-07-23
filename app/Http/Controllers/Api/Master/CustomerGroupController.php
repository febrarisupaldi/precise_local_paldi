<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Master\HelperController;
use Illuminate\Support\Facades\Validator;

class CustomerGroupController extends Controller
{
    private $customerGroup, $checkCustomerGroup;
    public function index()
    {
        $this->customerGroup = DB::select("select a.customer_group_id, a.group_code 'Kode group', a.group_name 'Nama group', a.group_description 'Keterangan group', ifnull(number_of_store, 0) 'Jumlah toko'
        , created_on 'Tanggal input', created_by 'User input', updated_on 'Tanggal update', updated_by 'User update'
        from precise.customer_group a
        left join (
            select customer_group_id, count(customer_id) number_of_store
            from precise.customer_customer_group
            group by customer_group_id
        ) b on a.customer_group_id = b.customer_group_id");
        return response()->json(["data" => $this->customerGroup]);
    }

    public function show($id)
    {
        $this->customerGroup = DB::table('customer_group')
            ->where('customer_group_id', $id)
            ->select(
                'customer_group_id',
                'group_code',
                'group_name',
                'group_description'
            )->get();
        return response()->json(["data" => $this->customerGroup]);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_code' => 'required|unique:customer_group',
            'group_name' => 'required',
            'created_by' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->checkCustomerGroup = DB::table('customer_group')->insert([
                'group_code' => $request->group_code,
                'group_name' => $request->group_name,
                'group_description' => $request->desc,
                'created_by' => $request->created_by
            ]);

            if ($this->checkCustomerGroup == 0) {
                return response()->json(['status' => 'error', 'message' => 'Failed to insert new customer group']);
            } else {
                return response()->json(['status' => 'ok', 'message' => 'customer group has been inserted']);
            }
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id' => 'required',
            'group_code' => 'required',
            'group_name' => 'required',
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
                $this->checkCustomerGroup = DB::table('customer_group')
                    ->where('customer_group_id', $request->group_id)
                    ->update([
                        'group_code' => $request->group_code,
                        'group_name' => $request->group_name,
                        'group_description' => $request->desc,
                        'updated_by' => $request->updated_by
                    ]);

                if ($this->checkCustomerGroup == 0) {
                    DB::rollBack();
                    return response()->json(['status' => 'error', 'message' => 'Failed to update customer group']);
                } else {
                    DB::commit();
                    return response()->json(['status' => 'ok', 'message' => 'customer group has been updated']);
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
            $this->checkCustomerGroup = DB::table('customer_group')
                > where('customer_group_id', $id)
                ->delete();
            if ($this->checkCustomerGroup == 0) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Failed to delete customer group, Contact your administrator']);
            } else {
                DB::commit();
                return response()->json(['status' => 'ok', 'message' => 'Customer group has been deleted']);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }



    public function check(Request $request)
    {
        $type = $request->get('type');
        $val = $request->get('id');
        if ($type == "code") {
            $this->customerGroup = DB::table('customer_group')->where('group_code', $val)->count();
        } elseif ($type == "name") {
            $this->customerGroup = DB::table('customer_group')->where('group_name', $val)->count();
        }
        return response()->json(['status' => 'ok', 'message' => $this->customerGroup]);
    }
}
