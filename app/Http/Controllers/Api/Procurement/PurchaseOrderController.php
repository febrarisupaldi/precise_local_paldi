<?php

namespace App\Http\Controllers\Api\Procurement;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\Auth\AuthController;

class PurchaseOrderController extends Controller
{
    private $purchaseOrder;
    public function index(Request $request)
    {
        $start = $request->get('start');
        $end = $request->get('end');
        $validator = Validator::make($request->all(), [
            'start' => 'required|date_format:Y-m-d|before_or_equal:end',
            'end'   => 'required|date_format:Y-m-d|after_or_equal:start'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $sql = DB::table('precise.purchase_order_hd as hd')
                ->whereBetween('purchase_order_date', [$start, $end])
                ->select(
                    'hd.purchase_order_hd_id',
                    DB::raw(
                        "SUM(dt.bruto) as 'Bruto',
                        SUM(dt.net) as 'Netto',
                        SUM(dt.ppn) as 'PPN',
                        SUM(dt.netto) as 'Total'"
                    )
                )->join('purchase_order_dt as dt', 'hd.purchase_order_hd_id', '=', 'dt.purchase_order_hd_id')
                ->whereBetween('hd.purchase_order_date', [$start, $end])
                ->groupBy('hd.purchase_order_hd_id')
                ->orderBy('hd.purchase_order_hd_Id');

            $this->purchaseOrder = DB::table(DB::raw("({$sql->toSql()})source"))
                ->mergeBindings($sql)
                ->select(
                    'source.purchase_order_hd_id',
                    'hd.purchase_order_number as Nomor order',
                    'hd.purchase_order_date as Tanggal order',
                    DB::raw("
                        CONCAT(purchase_type_code, ' - ', purchase_type_name) as 'Group PO',
                        CONCAT(sp.supplier_code, ' - ', sp.supplier_name) as 'Supplier'
                    "),
                    'hd.delivery_date1 as Tanggal kirim',
                    'hd.delivery_date2 as Tanggal sampai',
                    DB::raw("CONCAT(wr.warehouse_code,' - ', wr.warehouse_name) as 'Gudang'"),
                    'hd.purchase_order_toc as TOC',
                    'hd.purchase_order_currency as Currency',
                    'hd.purchase_order_currency_rate as Currency Rate',
                    'source.Bruto',
                    'source.Netto',
                    'source.PPN',
                    'source.Total',
                    DB::raw(
                        "CASE 
                            WHEN hd.ppn_type = 'E' THEN 'Exclude'
                            WHEN hd.ppn_type = 'I' THEN 'Include'
                            WHEN hd.ppn_type = 'N' THEN 'Non PPN'
                        ELSE
                            NULL
                        END as 'Tipe PPN'"
                    ),
                    'hd.purchase_order_description as Keterangan',
                    DB::raw("
                    CONCAT(hd.requested_by, ' - ', e.employee_name) as 'Requested by',
                    CASE 
                        WHEN hd.`po_status` = 'A' THEN 'Active'
                        WHEN hd.`po_status` = 'U' THEN 'Outstanding'
                        WHEN hd.`po_status` = 'X' THEN 'Close'
                        ELSE NULL
                    END as 'Status PO'
                    "),
                    'hd.created_on as Tanggal input',
                    'hd.created_by as Diinput oleh',
                    'hd.updated_on as Tanggal update',
                    'hd.updated_by as Diedit oleh'

                )
                ->leftJoin('precise.purchase_order_hd as hd', 'source.purchase_order_hd_id', '=', 'hd.purchase_order_hd_id')
                ->leftJoin('precise.supplier as sp', 'hd.supplier_id', '=', 'sp.supplier_id')
                ->leftJoin('precise.warehouse as wr', 'hd.warehouse_id', '=', 'wr.warehouse_id')
                ->leftJoin('precise.purchase_type as pt', 'hd.purchase_type_id', '=', 'pt.purchase_type_id')
                ->leftJoin('precise.employee as e', 'hd.requested_by', '=', 'e.employee_nik')
                ->get();

            return response()->json(["data" => $this->purchaseOrder]);
        }
    }

    public function show($id)
    {
        $this->purchaseOrder = DB::table('precise.purchase_order_hd as hd')
            ->where('hd.purchase_order_hd_id', $id)
            ->select(
                'hd.purchase_order_hd_id',
                'dt.POSeq as Seq',
                'p.PPNmbr as Nomor PR',
                'pr.product_code as Kode Barang',
                'pr.product_name as Nama Barang',
                'dt.purchase_order_qty as Qty',
                'dt.uom_code as UOM',
                'dt.purchase_order_std_qty as Qty std',
                'dt.uom_code_std as UOM std',
                'dt.purchase_order_price as Harga',
                'dt.bruto as Bruto',
                'dt.net as Net',
                'dt.ppn as PPN',
                'dt.netto as Total',
                'dt.packing_uom as Packing UOM',
                'hd.purchase_order_description as Keterangan',
                DB::raw("
                    CONCAT(hd.requested_by, ' - ', e.employee_name) 'Requested by',
                    CASE 
                        WHEN hd.`po_status` = 'A' THEN 'Active'
                        WHEN hd.`po_status` = 'U' THEN 'Outstanding'
                        WHEN hd.`po_status` = 'X' THEN 'Close'
                        ELSE NULL
                    END as 'Status PO'
                "),
                'dt.grn_qty as Qty GRN',
                'dt.grn_std_qty as Qty GRN std',
                'dt.due_date as Due Date',
                'hd.created_on as Tanggal input',
                'hd.created_by as Diinput oleh',
                'hd.updated_on as Tanggal update',
                'hd.updated_by as Diedit oleh'
            )->join('precise.purchase_order_dt as dt', 'hd.purchase_order_hd_id', '=', 'dt.purchase_order_hd_id')
            ->leftJoin('precise.purchase_request_dt as p', 'dt.purchase_request_dt_id', '=', 'p.purchase_request_dt_id')
            ->leftJoin('precise.product as pr', 'dt.product_id', '=', 'pr.product_id')
            ->leftJoin('precise.employee as e', 'hd.requested_by', '=', 'e.employee_nik')
            ->orderBy('hd.purchase_order_number')
            ->orderBy('dt.purchase_order_seq')
            ->get();

        return response()->json(["data" => $this->purchaseOrder]);
    }

    public function joined(Request $request)
    {
        $start = $request->get('start');
        $end = $request->get('end');
        $validator = Validator::make($request->all(), [
            'start' => 'required|date_format:Y-m-d|before_or_equal:end',
            'end'   => 'required|date_format:Y-m-d|after_or_equal:start'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->purchaseOrder = DB::table('purchase_order_hd as hd')
                ->whereBetween('purchase_order_date', [$start, $end])
                ->select(
                    'hd.purchase_order_hd_id',
                    'hd.purchase_order_number as Nomor PO',
                    'dt.POSeq as Seq',
                    'hd.purchase_order_date as Tanggal PO',
                    DB::raw("
                        CONCAT(pt.purchase_type_code, ' - ', pt.purchase_type_name) as 'Group PO',
                        CONCAT(sp.`supplier_code`, ' - ', sp.`supplier_name`) 'Supplier'
                    "),
                    'hd.delivery_date1 as Tanggal kirim',
                    'hd.delivery_date2 as Tanggal sampai',
                    DB::raw("CONCAT(wr.warehouse_code, ' - ', wr.warehouse_name) as 'Gudang'"),
                    'p.PPNmbr as Nomor PR',
                    'pr.product_code as Kode barang',
                    'pr.product_name as Nama barang',
                    'dt.purchase_order_qty as Qty',
                    'dt.uom_code as UOM',
                    'dt.purchase_order_std_qty as Qty std',
                    'dt.uom_code_std as UOM std',
                    'dt.packing_uom as Packing UOM',
                    'dt.purchase_order_price as Harga',
                    'dt.purchase_order_qty_price as Qty harga',
                    'dt.disc1 as Disc 1',
                    'dt.disc2 as Disc 2',
                    'dt.disc3 as Disc 3',
                    'dt.bruto as Bruto',
                    'dt.net as Net',
                    'dt.ppn as PPN',
                    'dt.netto as Netto',
                    'hd.purchase_order_description as Keterangan',
                    'dt.grn_qty as Qty GRN',
                    'dt.grn_std_qty as Qty GRN std',
                    DB::raw("
                        CONCAT(hd.requested_by, ' - ', e.employee_name) as 'Requested by',
                        CASE 
                            WHEN hd.`po_status` = 'A' THEN 'Active'
                            WHEN hd.`po_status` = 'U' THEN 'Outstanding'
                            WHEN hd.`po_status` = 'X' THEN 'Close'
                            ELSE NULL
                        END as 'Status PO'
                    "),
                    'hd.created_on as Tanggal input',
                    'hd.created_by as Diinput oleh',
                    'hd.updated_on as Tanggal update',
                    'hd.updated_by as Diedit oleh'
                )->join('precise.purchase_order_dt as dt', 'hd.purchase_order_number', '=', 'dt.PONmbr')
                ->leftJoin('precise.supplier as sp', 'hd.supplier_id', '=', 'sp.supplier_id')
                ->leftJoin('precise.warehouse as wr', 'hd.warehouse_id', '=', 'wr.warehouse_id')
                ->leftJoin('precise.purchase_request_dt as p', 'dt.purchase_request_dt_id', '=', 'p.purchase_request_dt_id')
                ->leftJoin('precise.product as pr', 'dt.product_id', '=', 'pr.product_id')
                ->leftJoin('precise.purchase_type as pt', 'hd.purchase_type_id', '=', 'pt.purchase_type_id')
                ->leftJoin('precise.cost_center as cc', 'dt.cost_center_id', '=', 'cc.cost_center_id')
                ->leftJoin('precise.employee as e', 'hd.requested_by', '=', 'e.employee_nik')
                ->orderBy('hd.purchase_order_number')
                ->orderBy('dt.purchase_order_seq')
                ->get();

            return response()->json(["data" => $this->purchaseOrder]);
        }
    }

    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $header = $request->toArray();
            $insert_header = DB::table('precise.oem_order_hd')
                ->insertGetId([
                    'oem_order_number' => $header["oem_order_number"],
                    'oem_order_date' => $header["oem_order_date"],
                    'customer_id' => $header["customer_id"],
                    'oem_order_description' => $header["oem_order_description"],
                    'oem_order_type_id' => $header["oem_order_type_id"],
                    'ppn_type' => $header["ppn_type"],
                    'created_by' => $header["created_by"]
                ]);

            $detail = $request->oem_order_detail;
            foreach ($detail as $key => $value) {
                $detail_data[] = [
                    'oem_order_hd_id' => $insert_header,
                    'oem_order_dt_seq' => $value["Seq"],
                    'product_customer_id' => $value["ProductCustomerID"],
                    'oem_order_qty' => $value["OrderQty"],
                    'due_date' => $value["DueDate"],
                    'loss_tolerance' => $value["LossTolerance"],
                    'created_by' => $value["created_by"]
                ];
            }
            DB::table('precise.oem_order_dt')->insert($detail_data);
            DB::commit();
            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function outstanding(Request $request)
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
            $sql = DB::table('precise.purchase_order_hd as hd')
                ->where('hd.po_status', '!=', 'X')
                ->whereBetween('hd.purchase_order_date', [$start, $end])
                ->select(
                    'hd.purchase_order_hd_id',
                    'dt.purchase_order_dt_id',
                    'dt.purchase_order_qty',
                    'dt.product_id',
                    'dt.uom_code',
                    DB::raw("SUM(IFNULL(gd.grn_qty, 0)) AS 'qtyTerima'")
                )
                ->join('precise.purchase_order_dt as dt', 'hd.purchase_order_hd_id', '=', 'dt.purchase_order_hd_id')
                ->leftJoin('precise.grn_dt as gd', 'dt.purchase_order_dt_id', '=', 'gd.purchase_order_dt_id')
                ->groupBy('hd.purchase_order_hd_id', 'dt.purchase_order_dt_id');

            $this->purchaseOrder = DB::table(DB::raw("({$sql->toSql()})source"))
                ->mergeBindings($sql)
                ->where(DB::raw("source.purchase_order_qty - source.qtyTerima"), '>', 0)
                ->select(
		    'hd.purchase_order_hd_id',
                    'hd.purchase_order_number as Nomor PO',
                    'hd.purchase_order_date as Tanggal PO',
		    'source.product_id',
                    'pr.product_code as Kode Barang',
                    'pr.product_name as Nama Barang',
                    'source.uom_code as UOM',
                    'source.purchase_order_qty as Jumlah Pesanan',
                    'source.qtyTerima as Jumlah Terima',
                    DB::raw("
                        source.purchase_order_qty - source.qtyTerima as Sisa,
                        TRUNCATE((source.`qtyTerima`/source.`purchase_order_qty`),2) 'Persentase',
                        CONCAT(pt.`purchase_type_code`, ' - ', pt.`purchase_type_name`) 'Group PO',
                        CONCAT(sp.`supplier_code`, ' - ', sp.`supplier_name`) 'Supplier',
                        CASE 
                            WHEN sp.`origin` = 'I' THEN 'Import'
                            WHEN sp.`origin` = 'L' THEN 'Lokal'
                            ELSE ''
                        END 'Group Supplier',
                        DATEDIFF(NOW(),hd.`purchase_order_date`) 'Umur PO (hari)',
                        CASE 
                            WHEN hd.`po_status` = 'A' THEN 'Approved'
                            WHEN hd.`po_status` = 'U' THEN 'Outstanding'
                            WHEN hd.`po_status` = 'X' THEN 'Close'
                            ELSE 'UNKNOWN'
                        END 'Status PO'	 
                    ")
                )
                ->leftJoin('precise.purchase_order_hd as hd', 'source.purchase_order_hd_id', '=', 'hd.purchase_order_hd_id')
                ->leftJoin('precise.supplier as sp', 'hd.supplier_id', '=', 'sp.supplier_id')
                ->leftJoin('precise.purchase_type as pt', 'hd.purchase_type_id', '=', 'pt.purchase_type_id')
                ->leftJoin('precise.product as pr', 'source.product_id', '=', 'pr.product_id')
                ->get();

            return response()->json(["data" => $this->purchaseOrder]);
        }
    }

    public function outstanding_detail(Request $request)
    {
        $po = $request->get('po');
        $product = $request->get('product');

        $validator = Validator::make($request->all(), [
            'po' => 'required',
            'product' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->purchaseOrder = DB::table('precise.purchase_order_hd as hd')
                ->where('hd.purchase_order_hd_id', $po)
                ->where('pr.product_id', $product)
                ->select(
                    'gh.grn_number as Nomor GRN',
                    'gh.grn_date as Tanggal GRN',
                    'gd.grn_qty as Jumlah kedatangan',
                    'dt.uom_code as UOM'
                )
                ->join('precise.purchase_order_dt as dt', 'hd.purchase_order_hd_id', '=', 'dt.purchase_order_hd_id')
                ->leftJoin('precise.grn_dt as gd', 'dt.purchase_order_dt_id', '=', 'gd.purchase_order_dt_id')
                ->leftJoin('precise.grn_hd as gh', 'gd.grn_hd_id', '=', 'gh.grn_hd_id')
                ->leftJoin('precise.product as pr', 'dt.product_id', '=', 'pr.product_id')
                ->get();

            return response()->json(["data" => $this->purchaseOrder]);
        }
    }
}
