<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Master\HelperController;
use Illuminate\Support\Facades\Validator;

class CustomerGroupMemberController extends Controller
{
    private $customerGroupMember, $checkCustomerGroupMember;
    public function show($id)
    {

        $this->customerGroup = DB::table('customer_group_member as a')
            ->where('a.customer_group_member_id', $id)
            ->select(
                'b.customer_id as Customer',
                'b.customer_name as Nama customer',
                'b.customer_alias_name as Nama alias customer',
                'city_name as Kota',
                'company_type_code as Tipe perusahaan',
                'retail_type_description as Tipe retail',
                'npwp as NPWP',
                'ppn_type as Tipe PPN',
                'a.created_on as Tanggal pemadanan',
                'a.created_by as Dipadankan oleh',
                'a.updated_on as Tanggal update',
                'a.updated_by as Diupdate oleh'
            )
            ->rightJoin('customer as b', 'a.customer_id', '=', 'b.customer_id')
            ->leftJoin('company_type as c', 'b.company_type_id', '=', 'c.company_type_id')
            ->leftJoin('retail_type as d', 'b.retail_type_id', '=', 'd.retail_type_id')
            ->leftJoin('city as e', 'b.city_id', '=', 'e.city_id')
            ->get();
        return response()->json(["data" => $this->customerGroupMember]);
    }

    public function create(Request $request)
    {
        $helper = new HelperController();
        if ($helper->insertOrUpdate($request->data, 'customer_group_member', '') == false) {
            return response()->json(['status' => 'error', 'message' => 'Failed to add Member customer group']);
        } else {
            return response()->json(['status' => 'ok', 'message' => 'Member customer group have beed added']);
        }
    }

    public function destroy($id)
    {
        $id = explode("-", $id);
        DB::beginTransaction();
        try {
            $helper = new HelperController();
            $helper->reason("delete");

            $this->checkCustomerGroupMember = DB::table('customer_group_member')
                ->whereIn('customer_group_member_id', $id)
                ->delete();
            if ($this->checkCustomerGroupMember == 0) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Failed to delete Member customer group']);
            } else {
                DB::commit();
                return response()->json(['status' => 'ok', 'message' => 'Member customer group have beed deleted']);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function check(Request $request)
    {
        $customer_group = $request->get('customer_group_id');
        $customer = $request->get('customer_id');
        $validator = Validator::make($request->all(), [
            'customer_group_id' => 'required',
            'customer_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->customerGroup = DB::table('customer_group_member')
                ->where(
                    [
                        'customer_group_member_id' => $customer_group,
                        'customer_id' => $customer
                    ]
                )->count();
            return response()->json(["test" => $this->checkCustomerGroupMember]);
        }
    }
}
