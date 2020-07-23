<?php

namespace App\Http\Controllers\Api\Sales;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class SalesOrderController extends Controller
{
    private $salesOrder, $checkSalesOrder;
    public function index(Request $request)
    {
        $start = $request->get('start');
        $end = $request->get('end');
        $validator = Validator::make($request->all(), [
            'start' => 'required|date_format:Y-m-d|before_or_equal:end',
            'end' => 'required|date_format:Y-m-d|after_or_equal:start'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->salesOrder = DB::table('sales_order_hd as a')
                ->whereBetween('sales_order_date', [$start, $end])
                ->select(
                    'a.sales_order_hd_id',
                    'b.sales_order_seq as Seq',
                    'product_code as Kode barang',
                    'product_name as Nama barang',
                    'sales_order_qty as SO Qty',
                    'b.uom_code as UOM',
                    'price as Price',
                    'qty_price as Qty price',
                    'percent_disc1 as % Disc 1',
                    'percent_disc2 as % Disc 2',
                    'percent_disc3 as % Disc 3',
                    'disc1 as Disc 1',
                    'disc2 as Disc 2',
                    'disc3 as Disc 3',
                    DB::raw("disc1 + disc2 + disc3 as 'Total Disc'"),
                    'after_disc as After disc',
                    'bruto as Bruto',
                    'netto as Netto',
                    'ppn as PPN',
                    'net as Net'
                )->join('sales_order_dt as b', 'a.sales_order_hd_id', '=', 'b.sales_order_hd_id')
                ->leftJoin('product as c', 'b.product_id', '=', 'c.product_id')
                ->get();
            return response()->json(["data" => $this->salesOrder]);
        }
    }


    public function show($id)
    {
        $this->salesOrder = DB::table('sales_order_hd as a')
            ->where('sales_order_hd_id', $id)
            ->select(
                'sales_order_number',
                DB::raw("
                    get_friendly_date(sales_order_date) as 'sales_order_date',
                    concat(customer_code, ' - ', customer_name) as 'customer',
                    concat(warehouse_code, ' - ', warehouse_name) as 'warehouse'"),
                'purchase_order_number',
                'sales_order_description',
                DB::raw("
                    case
                    when a.ppn_type = 'I' then 'Include'
                    when a.ppn_type = 'E' then 'Exclude'
                    when a.ppn_type = 'N' then 'Non PPN'
                end as ppn_type
                , case when sales_order_status = 'A' then 'Approved'
                    when sales_order_status = 'C' then 'Cancel'
                    when sales_order_status = 'O' then 'Open'
                    when sales_order_status = 'U' then 'Outstanding'
                    when sales_order_status = 'X' then 'Close'
                end as 'status'
                , case 
                    when employee_name is null then sales_person
                    when employee_name is not null then concat(sales_person, ' - ', employee_name)
                end as sales_person")
            )->leftJoin('customer as b', 'a.customer_id', '=', 'b.customer_id')
            ->leftJoin('warehouse as c', 'a.warehouse_id', '=', 'c.warehouse_id')
            ->leftJoin('employee as d', 'a.sales_person', '=', 'd.employee_nik')
            ->get();

        return response()->json(["data" => $this->salesOrder]);
    }

    public function outstanding($id)
    {
        $id = explode("-", $id);

        $sql = DB::table('sales_order_hd as sohd')
            ->whereIn('sohd.sales_order_hd_id', $id)
            ->select(
                'sodt.sales_order_dt_id',
                'sodt.sales_order_number',
                'sodt.sales_order_seq',
                'sodt.product_id',
                'prod.product_code',
                'prod.product_name',
                'sodt.sales_order_qty',
                'sodt.uom_code'
            )
            ->join('sales_order_dt as sodt', 'sohd.sales_order_hd_id', '=', 'sodt.sales_order_hd_id')
            ->leftJoin('product as prod', 'sodt.product_id', '=', 'prod.product_id');

        $sql2 = DB::table('sales_order_hd as sohd')
            ->whereIn('sohd.sales_order_hd_id', $id)
            ->select(
                'dodt.sales_order_number',
                'dodt.sales_order_seq',
                DB::raw("sum(dodt.delivery_order_qty) as totalDO"),
                'uom_code'
            )
            ->leftJoin('delivery_order_dt as dodt', 'sohd.sales_order_number', '=', 'dodt.sales_order_number')
            ->groupBy('dodt.sales_order_number', 'dodt.sales_order_seq', 'uom_code');

        $this->salesOrder = DB::table(DB::raw("({$sql->toSql()})SO"))
            ->mergeBindings($sql)
            ->select(
                'SO.sales_order_dt_id',
                'SO.sales_order_number',
                'SO.sales_order_seq as Seq',
                'SO.product_id',
                'product_code as Kode barang',
                'product_name as Nama barang',
                'sales_order_qty as Qty SO',
                'SO.uom_code as UOM',
                DB::raw("ifnull(SJ.totalDO, 0) 'Total DO',sales_order_qty - ifnull(SJ.totalDO, 0) as 'Qty outstanding'")
            )->leftJoin(DB::raw("({$sql2->toSql()})SJ"), function ($join) {
                $join->on('SO.sales_order_number', '=', 'SJ.sales_order_number');
                $join->on('SO.sales_order_seq', '=', 'SJ.sales_order_seq');
                $join->on('SO.uom_code', '=', 'SJ.uom_code');
            })
            ->mergeBindings($sql2)
            ->whereRaw('ifnull(SJ.totalDO, 0) < SO.sales_order_qty')
            ->orderBy('SO.sales_order_number')
            ->orderBy('SO.sales_order_seq')
            ->get();
        return response()->json(["data" => $this->salesOrder]);
    }

    public function process($id)
    {
        $id = explode('-', $id);
        $this->salesOrder = DB::table('monitoring_so_hd as hd')
            ->where('hd.sales_order_hd_id', $id)
            ->whereBetween('pick_up_status_id', [1, 3])
            ->orWhere('pick_up_status_id', '>', 7)
            ->selectRaw('concat(stat.status_name, ' - ', sales_order_number, ' - ', customer_name) as SOInProcess')
            ->leftJoin('sales_order_hd as sohd', 'hd.sales_order_hd_id', '=', 'sohd.sales_order_hd_id')
            ->leftJoin('customer as cus', 'sohd_customer_id', '=', 'cus._customer_id')
            ->leftJoin('pick_up_status as stat', 'hd_pick_up_status_id', '=', 'stat.status_id')
            ->get();

        return response()->json(["data" => $this->salesOrder]);
    }

    public function close($id)
    {
        $id = explode('-', $id);
        $this->salesOrder = DB::table('sales_order_hd')
            ->select('sales_order_number')
            ->whereIn('sales_order_hd_id', $id)
            ->where('sales_order_status', 'X')
            ->get();

        return response()->json(["data" => $this->salesOrder]);
    }
    public function detail(Request $request)
    {
        $start = $request->get('start');
        $end = $request->get('end');
        $validator = Validator::make($request->all(), [
            'start' => 'required|date_format:Y-m-d|before_or_equal:end',
            'end' => 'required|date_format:Y-m-d|after_or_equal:start'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->salesOrder = DB::table('sales_order_hd as a')
                ->whereBetween('sales_order_date', [$start, $end])
                ->select(
                    'a.sales_order_hd_id',
                    'b.sales_order_seq as Seq',
                    'product_code as Kode barang',
                    'product_name as Nama barang',
                    'sales_order_qty as SO Qty',
                    'b.uom_code as UOM',
                    'price as Price',
                    'qty_price as Qty price',
                    'percent_disc1 as % Disc 1',
                    'percent_disc2 as % Disc 2',
                    'percent_disc3 as % Disc 3',
                    'disc1 as Disc 1',
                    'disc2 as Disc 2',
                    'disc3 as Disc 3',
                    DB::raw("disc1 + disc2 + disc3 as 'Total Disc'"),
                    'after_disc as After disc',
                    'bruto as Bruto',
                    'netto as Netto',
                    'ppn as PPN',
                    'net as Net'
                )->join('sales_order_dt as b', 'a.sales_order_hd_id', '=', 'b.sales_order_hd_id')
                ->leftJoin('product as c', 'b.product_id', '=', 'c.product_id')
                ->get();

            return response()->json(["data" => $this->salesOrder]);
        }
    }

    public function detail_show($id)
    {
        $this->salesOrder = DB::table('sales_order_dt as a')
            ->where('sales_order_hd_id', $id)
            ->select(
                'sales_order_seq as Seq',
                'product_code as Kode barang',
                'product_name as Nama barang',
                'sales_order_qty as SO Qty',
                'b.uom_code as UOM',
                'price as Price',
                'qty_price as Qty price',
                'percent_disc1 as % Disc 1',
                'percent_disc2 as % Disc 2',
                'percent_disc3 as % Disc 3',
                'disc1 as Disc 1',
                'disc2 as Disc 2',
                'disc3 as Disc 3',
                DB::raw("disc1 + disc2 + disc3 as 'Total Disc'"),
                'after_disc as After disc',
                'bruto as Bruto',
                'netto as Netto',
                'ppn as PPN',
                'net as Net'
            )->leftJoin('product as b', 'a.product_id', '=', 'b.product_id')
            ->get();

        return response()->json(["data" => $this->salesOrder]);
    }

    public function joined(Request $request)
    {
        $start = $request->get('start');
        $end = $request->get('end');
        $end = $request->get('end');
        $validator = Validator::make($request->all(), [
            'start' => 'required|date_format:Y-m-d|before_or_equal:end',
            'end' => 'required|date_format:Y-m-d|after_or_equal:start'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->salesOrder = DB::table('sales_order_hd as a')
                ->whereBetween('sales_order_date', [$start, $end])
                ->select(
                    'a.sales_order_hd_id',
                    'a.sales_order_number as Nomor SO',
                    'sales_order_date as Tanggal SO',
                    'customer_code as Kode customer',
                    'customer_name as Nama customer',
                    'warehouse_code as Kode gudang',
                    'warehouse_name as Nama gudang',
                    'purchase_order_number as Nomor PO',
                    'sales_order_description as Keterangan',
                    DB::raw("case
                    when a.ppn_type = 'I' then 'Include'
                    when a.ppn_type = 'E' then 'Exclude'
                    when a.ppn_type = 'N' then 'Non PPN'
                end as 'Tipe PPN'
                , case when sales_order_status = 'A' then 'Approved'
                    when sales_order_status = 'C' then 'Cancel'
                    when sales_order_status = 'O' then 'Open'
                    when sales_order_status = 'U' then 'Outstanding'
                    when sales_order_status = 'X' then 'Close'
                end as 'Status',
                concat(sales_person, ' - ', ifnull(employee_name, '')) 'Sales person'"),
                    'sales_order_seq as Seq',
                    'product_code as Kode barang',
                    'product_name as Nama barang',
                    'sales_order_qty as Qty SO',
                    'b.uom_code as UOM',
                    'price as Price',
                    'qty_price as Qty Price',
                    'percent_disc1 as % Disc 1',
                    'percent_disc2 as % Disc 2',
                    'percent_disc3 as % Disc 3',
                    'disc1 as Disc 1',
                    'disc2 as Disc 2',
                    'disc3 as Disc 3',
                    'after_disc as After Disc',
                    'bruto as Bruto',
                    'netto as Netto',
                    'ppn as PPN',
                    'net as Net',
                    'b.created_on as Tanggal input',
                    'b.created_by as Input oleh',
                    'b.updated_on as Tanggal update',
                    'b.updated_by as Update oleh'
                )->join('sales_order_dt as b', 'a.sales_order_hd_id', '=', 'b.sales_order_hd_id')
                ->leftJoin('customer as c', 'a.customer_id', '=', 'c.customer_id')
                ->leftJoin('product as d', 'b.product_id', '=', 'd.product_id')
                ->leftJoin('warehouse as e', 'a.warehouse_id', '=', 'e.warehouse_id')
                ->leftJoin('employee as f', 'a.sales_person', '=', 'f.employee_nik')
                ->get();

            return response()->json(["data" => $this->salesOrder]);
        }
    }

    public function release(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sales_order_hd_id' => 'required|exists:sales_order_hd,sales_order_hd_id',
            'released_on' => 'required|date_format:Y-m-d',
            'released_by' => 'required|exists:users,user_id',
            'pick_up_priority' => 'required|exists:pick_up_priority,pick_up_priority_id',
            'created_by' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            return response()->json(["data" => $request->data]);
        }
    }
}
