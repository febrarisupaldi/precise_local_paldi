<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    private $customer, $checkCustomer;
    public function index()
    {
        $this->customer = DB::table('customer as a')
            ->select(
                'customer_id',
                'customer_code as Kode customer',
                'customer_name as Nama customer',
                'c.city_name as Kota',
                'b.retail_type_description as Tipe retail'
            )
            ->leftJoin('retail_type as b', 'a.retail_type_id', '=', 'b.retail_type_id')
            ->leftJoin('city as c', 'a.city_id', '=', 'c.city_id')
            ->get();
        return response()->json(["data" => $this->customer]);
    }

    public function show($id)
    {
        $this->customer = DB::table('customer')
            ->where('customer_id', $id)
            ->select(
                'customer_code',
                'customer_name',
                'customer_alias_name',
                'company_type_id',
                'retail_type_id',
                'npwp',
                'pkp_name',
                'ppn_type',
                'city_id',
                'ar_coa_id',
                'is_active'
            )->get();
        return response()->json(["data" => $this->customer]);
    }

    public function showByRetail($id)
    {
        $value = explode("-", $id);
        $this->customer = DB::table('customer as a')
            ->whereIn('b.retail_type_id', $value)
            ->select(
                'customer_id',
                'customer_code as Kode customer',
                'customer_name as Nama customer',
                'a.customer_alias_name as Nama alias customer',
                'c.city_name as Kota',
                'b.retail_type_code as Tipe retail'
            )
            ->leftJoin('retail_type as b', 'a.retail_type_id', '=', 'b.retail_type_id')
            ->leftJoin('city as c', 'a.city_id', '=', 'c.city_id')
            ->orderBy('customer_id')
            ->get();
        return response()->json(["data" => $this->customer]);
    }
}
