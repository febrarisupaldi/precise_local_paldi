<?php

namespace App\Http\Controllers\Api\Procurement;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PurchaseRequestController extends Controller
{
    private $purchaseRequest;
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
            $this->purchaseRequest = DB::table('purchase_request_hd as hd')
                ->whereBetween('hd.purchase_request_date', [$start, $end])
                ->select(
                    'hd.purchase_request_hd_id',
                    'purchase_request_number as Nomor PR',
                    'purchase_request_date as Tanggal PR',
                    'purchase_request_need_date as Tanggal kebutuhan',
                    DB::raw(
                        "CONCAT(purchase_type_code, ' - ', purchase_type_name) as 'Group PR',
                        concat(cost_center_code, ' - ', cost_center_name) as 'Cost center',
                        concat(requested_by, ' - ', e.employee_name) as 'Requested by',
                        CASE 
                            WHEN approval_status = 'A' THEN 'Approved'
                            WHEN approval_status = 'U' THEN 'Outstanding'
                            WHEN approval_status = 'X' THEN 'Close'
                        ELSE
                            NULL
                        END as 'Status',
                        concat(approved_by, ' - ', e2.employee_name) as 'Approved by'"
                    ),
                    'hd.purchase_request_description as Deskripsi',
                    'hd.created_on as Tanggal input',
                    'hd.created_by as User input',
                    'hd.updated_on as Tanggal update',
                    'hd.updated_by as User update'

                )
                ->join('purchase_request_dt as dt', 'hd.purchase_request_hd_id', '=', 'dt.purchase_request_hd_id')
                ->leftJoin('cost_center as cc', 'hd.cost_center_id', '=', 'cc.cost_center_id')
                ->leftJoin('product as p', 'dt.product_id', '=', 'p.product_id')
                ->leftJoin('purchase_type as pt', 'hd.purchase_type_id', '=', 'pt.purchase_type_id')
                ->leftJoin('employee as e', 'hd.requested_by', '=', 'e.employee_nik')
                ->leftJoin('employee as e2', 'hd.requested_by', '=', 'e2.employee_nik')
                ->orderBy('hd.purchase_request_hd_id')
                ->get();

            return response()->json(["data" => $this->purchaseRequest]);
        }
    }

    public function show($id)
    {
        $this->purchaseRequest = DB::table('purchase_request_hd as hd')
            ->where('hd.purchase_request_hd_id', $id)
            ->select(
                'hd.purchase_request_hd_id',
                'dt.purchase_request_seq as Seq',
                'p.product_code as Kode barang',
                'p.product_name as Nama barang',
                'dt.purchase_request_qty as Qty PR',
                'dt.uom_code as UOM',
                'dt.purchase_request_std_qty as Qty PR Std',
                'dt.uom_code_std as UOM Std',
                'dt.due_date as Due date',
                DB::raw(
                    "CASE 
                        WHEN dt.purchase_request_status = 'O' THEN 'Open'
                        WHEN dt.purchase_request_status = 'U' THEN 'Outstanding'
                        WHEN dt.purchase_request_status = 'X' THEN 'Closed'
                        ELSE NULL
                    END as'Status PR'"
                ),
                'hd.created_on as Tanggal input',
                'hd.created_by as Diinput oleh',
                'hd.updated_on as Tanggal update',
                'hd.updated_by as Diedit oleh'
            )
            ->join('purchase_request_dt as dt', 'hd.purchase_request_hd_id', '=', 'dt.purchase_request_hd_id')
            ->leftJoin('product as p', 'dt.product_id', '=', 'p.product_id')
            ->leftJoin('purchase_type as pt', 'hd.purchase_type_id', '=', 'pt.purchase_type_id')
            ->leftJoin('cost_center as cc', 'hd.cost_center_id', '=', 'cc.cost_center_id')
            ->leftJoin('employee as e', 'hd.requested_by', '=', 'e.employee_nik')
            ->leftJoin('employee as e2', 'hd.requested_by', '=', 'e2.employee_nik')
            ->orderBy('hd.purchase_request_number')
            ->orderBy('dt.purchase_request_seq')
            ->get();

        return response()->json(["data" => $this->purchaseRequest]);
    }

    public function joined(Request $request)
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
            $this->purchaseRequest = DB::table('purchase_request_hd as hd')
                ->whereBetween('purchase_request_date', [$start, $end])
                ->select(
                    'hd.purchase_request_hd_id',
                    'purchase_request_number as Nomor PR',
                    'purchase_request_date as Tanggal PR',
                    'purchase_request_need_date as Tanggal kebutuhan',
                    DB::raw("
                        CONCAT(purchase_type_code, ' - ', purchase_type_name) as 'Group PR',
                        concat(cost_center_code, ' - ', cost_center_name) as 'Cost center',
                        concat(requested_by, ' - ', e.employee_name) as 'Requested by',
                        CASE 
                            WHEN hd.approval_status = 'A' THEN 'Approved'
                            WHEN hd.approval_status = 'U' THEN 'Outstanding'
                            WHEN hd.approval_status = 'X' THEN 'Closed'
                        ELSE 
                            NULL
                        END as'Status',
                        concat(approved_by, ' - ', e2.employee_name) as 'Approved By'
                    "),
                    'hd.purchase_request_description as Deskripsi',
                    'dt.purchase_request_seq as Seq',
                    'p.product_code as Kode barang',
                    'p.product_name as Nama barang',
                    'dt.purchase_request_qty as Qty PR',
                    'dt.uom_code as UOM',
                    'dt.purchase_request_std_qty as Qty PR Std',
                    'dt.uom_code_std as UOM Std',
                    'dt.due_date as Due date',
                    DB::raw(
                        "CASE 
                            WHEN dt.purchase_request_status = 'O' THEN 'Open'
                            WHEN dt.purchase_request_status = 'U' THEN 'Outstanding'
                            WHEN dt.purchase_request_status = 'X' THEN 'Closed'
                            ELSE NULL
                        END as'Status PR'"
                    ),
                    'hd.created_on as Tanggal input',
                    'hd.created_by as Diinput oleh',
                    'hd.updated_on as Tanggal update',
                    'hd.updated_by as Diedit oleh'
                )
                ->join('purchase_request_dt as dt', 'hd.purchase_request_hd_id', '=', 'dt.purchase_request_hd_id')
                ->leftJoin('product as p', 'dt.product_id', '=', 'p.product_id')
                ->leftJoin('purchase_type as pt', 'hd.purchase_type_id', '=', 'pt.purchase_type_id')
                ->leftJoin('cost_center as cc', 'hd.cost_center_id', '=', 'cc.cost_center_id')
                ->leftJoin('employee as e', 'hd.requested_by', '=', 'e.employee_nik')
                ->leftJoin('employee as e2', 'hd.requested_by', '=', 'e2.employee_nik')
                ->orderBy('hd.purchase_request_number')
                ->orderBy('dt.purchase_request_seq')
                ->get();

            return response()->json(["data" => $this->purchaseRequest]);
        }
    }
}
