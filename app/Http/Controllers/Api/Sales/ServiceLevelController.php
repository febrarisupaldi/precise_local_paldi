<?php

namespace App\Http\Controllers\Api\Sales;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ServiceLevelController extends Controller
{
    private $serviceLevel;
    public function show($id)
    {
        $this->serviceLevel = DB::table('sales_order_hd as a')
            ->where('sales_order_number', $id)
            ->select(
                DB::raw("get_friendly_date(sales_order_date) 'sales_order_date'"),
                'b.warehouse_name',
                DB::raw("concat(c.customer_code, ' - ', c.customer_name) as customer,
            case when sales_order_status = 'A' then 'Approved'
                when sales_order_status = 'O' then 'Open'
                when sales_order_status = 'X' then 'Close'
                when sales_order_status = 'U' then 'Outstanding'
                when sales_order_status = 'C' then 'Cancel'
            end as StatusSO,
            ifnull(employee_name, sales_person) sales_person")
            )
            ->leftJoin('precise.warehouse as b', 'a.warehouse_id', '=', 'b.warehouse_id')
            ->leftJoin('precise.customer as c', 'a.customer_id', '=', 'c.customer_id')
            ->leftJoin('precise.employee as d', 'a.sales_person', '=', 'd.employee_nik')
            ->get();

        return response()->json(["data" => $this->serviceLevel]);
    }
}
