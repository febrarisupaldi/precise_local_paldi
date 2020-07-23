<?php

namespace App\Http\Controllers\Api\OEM;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\Master\HelperController;
use App\Http\Controllers\Api\Helpers\QueryController;

class PurchaseOrderController extends Controller
{
    private $purchaseOrder, $checkPurchaseOrder;
    public function index(Request $request)
    {
        $start = $request->get('start');
        $end = $request->get('end');
        $wh = $request->get('warehouse');
        $cust = $request->get('customer');

        $validator = Validator::make($request->all(), [
            'start' => 'required|date_format:Y-m-d|before_or_equal:end',
            'end' => 'required|date_format:Y-m-d|after_or_equal:start',
            'warehouse' => 'required',
            'customer' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $warehouse = explode('-', $wh);
            $customer = explode('-', $cust);
            $this->purchaseOrder = DB::table('precise.oem_order_hd as hd')
            ->whereBetween('hd.oem_order_date',[$start, $end])
            ->whereIn('hd.warehouse_id',$warehouse)
            ->whereIn('hd.customer_id', $customer)
            ->select(
                'oem_order_hd_id',
                'oem_order_number',
                'oem_order_date', 
                'hd.customer_id',
                'customer_code',
                'customer_name', 
                'hd.warehouse_id',
                'wh.warehouse_code',
                'wh.warehouse_name',
                'hd.shipping_address_id',
                'ca.address',
                'oem_order_description',
                'hd.oem_order_type_id',
                'oem_order_type_name', 
                'hd.ppn_type',
                DB::raw("
                    case hd.ppn_type
                    when 'I' then 'Include' 
                    when 'E' then 'Exclude' 
                    when 'N' then 'Non PPN' else 'Unknown'		
                end as ppn_type, 
                case oem_order_status 
                    when 'A' then 'Aktif'
                    when 'X' then 'Close'
                    when 'F' then 'Freeze'
                    when 'H' then 'Hold' else 'Unknown'
                end as oem_order_status
                "),
                'hd.created_on',
                'hd.created_by',
                'hd.updated_on',
                'hd.updated_by'
            )
            ->leftJoin('precise.customer as cust','hd.customer_id','=', 'cust.customer_id')
            ->leftJoin('precise.oem_order_type as ot', 'hd.oem_order_type_id', '=', 'ot.oem_order_type_id')
            ->leftJoin('precise.warehouse as wh', 'hd.warehouse_id', '=', 'wh.warehouse_id')
            ->leftJoin('precise.customer_address as ca', 'hd.shipping_address_id', '=', 'ca.customer_address_id')
            ->get();

            return response()->json(['data'=>$this->purchaseOrder], 200);
        }
    }

    public function show($id){
        try {
            $master = DB::table('precise.oem_order_hd as hd')
            ->where('hd.oem_order_hd_id', $id)
            ->select(
                'oem_order_hd_id',
                'oem_order_number',
                'oem_order_date',
                'hd.customer_id',
                'customer_code',
                'customer_name', 
                'oem_order_description',
                'hd.oem_order_type_id',
                'oem_order_type_name',
                'oem_order_status',
                'hd.warehouse_id',
                'warehouse_code',
                'warehouse_name',
                'hd.shipping_address_id',
                'ca.address',
                'hd.ppn_type',
                'hd.created_on',
                'hd.created_by',
                'hd.updated_on',
                'hd.updated_by'
            )
            ->leftJoin('precise.customer as cust','hd.customer_id','=', 'cust.customer_id')
            ->leftJoin('precise.oem_order_type as ot', 'hd.oem_order_type_id', '=', 'ot.oem_order_type_id')
            ->leftJoin('precise.warehouse as wh', 'hd.warehouse_id', '=', 'wh.warehouse_id')
            ->leftJoin('precise.customer_address as ca', 'hd.shipping_address_id', '=', 'ca.customer_address_id')
            ->first();

            $detail = DB::table('precise.oem_order_hd as hd')
            ->where('hd.oem_order_hd_id', $master->oem_order_hd_id)
            ->select(
                'hd.oem_order_hd_id',
                'dt.oem_order_dt_id',
                'dt.oem_order_dt_seq',
                'dt.product_customer_id',
                'pc.is_order_qty_include_reject',
                DB::raw("
                    count(odd.oem_delivery_dt_id) as delivery_count
                "),
                'pc.product_id',
                'p.product_code',
                'p.product_name',
                'hd.customer_id',
                'customer_code',
                'customer_name',
                'dt.oem_order_qty', 
                'v.sum_delivery_qty as total_delivery_qty',
                'v.sum_on_going_qty as total_on_going_qty',
                'v.sum_received_qty as total_received_qty',
                'v.outstanding_qty',
                'p.uom_code',
                'dt.due_date',
                'dt.loss_tolerance',
                'dt.created_on',
                'dt.created_by',
                'dt.updated_on',
                'dt.updated_by'
            )
            ->join('precise.oem_order_dt as dt', 'hd.oem_order_hd_id', '=', 'dt.oem_order_hd_id')
            ->leftJoin('precise.oem_delivery_dt as odd', 'dt.oem_order_dt_id', '=', 'odd.oem_order_dt_id')
            ->leftJoin('precise.product_customer as pc', 'dt.product_customer_id', '=', 'pc.product_customer_id')
            ->leftJoin('precise.customer as c', 'hd.customer_id', '=', 'c.customer_id')
            ->leftJoin('precise.product as p', 'pc.product_id', '=', 'p.product_id')
            ->leftJoin('precise.warehouse as w', 'hd.warehouse_id', '=', 'w.warehouse_id')
            ->leftJoin('precise.view_oem_outstanding_po as v', function($join){
                $join->on('hd.oem_order_hd_id', '=', 'v.oem_order_hd_id')
                ->on('dt.oem_order_dt_id', '=', 'v.oem_order_dt_id');
            })
            ->groupBy('hd.oem_order_hd_id','dt.oem_order_dt_id')
            ->get();

            $this->purchaseOrder = array(
                'oem_order_hd_id'       => $master->oem_order_hd_id,
                'oem_order_number'      => $master->oem_order_number,
                'oem_order_date'        => $master->oem_order_date,
                'customer_id'        => $master->customer_id,
                'customer_code'         => $master->customer_code,
                'customer_name'         => $master->customer_name, 
                'oem_order_description' => $master->oem_order_description,
                'oem_order_type_id'  => $master->oem_order_type_id,
                'oem_order_type_name'   => $master->oem_order_type_name,
                'oem_order_status'      => $master->oem_order_status,
                'warehouse_id'       => $master->warehouse_id,
                'warehouse_code'        => $master->warehouse_code,
                'warehouse_name'        => $master->warehouse_name,
                'shipping_address_id'=> $master->shipping_address_id,
                'address'            => $master->address,
                'ppn_type'           => $master->ppn_type,
                'created_on'         => $master->created_on,
                'created_by'         => $master->created_by,
                'updated_on'         => $master->updated_on,
                'updated_by'         => $master->updated_by,
                'detail'                => $detail
            );
            return response()->json($this->purchaseOrder, 200);
        }catch(\Exception $e){
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }

        
    }

    public function create(Request $request){
        $data = $request->json()->all();

        $validator = Validator::make(json_decode(json_encode($data),true),[
            'oem_order_number'   => 'required',
            'oem_order_date'     => 'required',
            'customer_id'        => 'required|exists:customer,customer_id',
            'oem_order_type_id'  => 'required|exists:oem_order_type,oem_order_type_id',
            'warehouse_id'       => 'required|exists:warehouse, warehouse_id',
            'shipping_address_id'=> 'required|exists:customer_address, customer_address_id',
            'created_by'         => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            DB::beginTransaction();
            try{
                $id = DB::table('precise.oem_order_hd')
                ->insertGetId([
                    'oem_order_number'      => $data['oem_order_number'],
                    'oem_order_date'        => $data['oem_order_date'],
                    'customer_id'           => $data['customer_id'],
                    'oem_order_description' => $data['oem_order_description'],
                    'oem_order_type'        => $data['oem_order_type_id'],
                    'oem_order_status'      => $data['oem_order_status'],
                    'warehouse_id'          => $data['warehouse_id'],
                    'shipping_address_id'   => $data['shipping_address_id'],
                    'ppn_type'              => $data['ppn_type'],
                    'created_by'            => $data['created_by']
                ]);

                foreach($data['detail'] as $d){
                    $dt[] = [
                        'oem_order_hd_id'    => $id,
                        'oem_order_dt_seq'   => $d['oem_order_dt_seq'],
                        'product_customer_id'=> $d['product_customer_id'],
                        'oem_order_qty'      => $d['oem_order_qty'],
                        'due_date'           => $d['due_date'],
                        'loss_tolerance'     => $d['loss_tolerance'],
                        'created_by'         => $d['created_by']
                    ];
                }

                DB::table('precise.oem_order_dt')
                ->insert($dt);

                $trans = DB::table('precise.oem_order_hd')
                        ->where('oem_order_hd_id', $id)
                        ->select('oem_order_number')
                        ->first();

                DB::commit();
                return response()->json(['status' => 'ok', 'message' => $trans->oem_order_number], 200);
            }catch(\Exception $e){
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
    }

    public function update(Request $request){
        $data = $request->json()->all();

        $validator = Validator::make(json_decode(json_encode($data),true),[
            'oem_order_number'   => 'required',
            'oem_order_date'     => 'required',
            'customer_id'        => 'required|exists:customer,customer_id',
            'oem_order_type_id'  => 'required|exists:oem_order_type,oem_order_type_id',
            'warehouse_id'       => 'required|exists:warehouse, warehouse_id',
            'shipping_address_id'=> 'required|exists:customer_address, customer_address_id',
            'updated_by'         => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            DB::beginTransaction();
            try{
                QueryController::reason($data);
                DB::table('precise.oem_order_hd')
                ->where('oem_order_hd_id', $data['oem_order_hd_id'])
                ->update([
                    'oem_order_number'      => $data['oem_order_number'],
                    'oem_order_date'        => $data['oem_order_date'],
                    'customer_id'           => $data['customer_id'],
                    'oem_order_description' => $data['oem_order_description'],
                    'oem_order_type_id'     => $data['oem_order_type_id'],
                    'warehouse_id'          => $data['warehouse_id'],
                    'shipping_address_id'   => $data['shipping_address_id'],
                    'ppn_type'              => $data['ppn_type'],
                    'created_by'            => $data['created_by']
                ]);

                if($data['inserted'] != null)
                {
                    foreach($data['inserted'] as $d)
                    {
                        $dt[] = [
                            'oem_order_hd_id'       => $d['oem_order_hd_id'],
                            'oem_order_dt_seq'      => $d['oem_order_dt_seq'],
                            'product_customer_id'   => $d['product_customer_id'],
                            'oem_order_qty'         => $d['oem_order_qty'],
                            'due_date'              => $d['due_date'],
                            'loss_tolerance'        => $d['loss_tolerance'],
                            'created_by'            => $d['created_by']
                        ];
                    }
                    DB::table('precise.oem_order_dt')
                    ->insert($dt);
                }

                if($data['updated'] != null)
                {
                    foreach($data['updated'] as $d)
                    {
                        DB::table('precise.oem_order_dt')
                        ->where('oem_order_dt_id', $d['oem_order_dt_id'])
                        ->update([
                            'oem_order_hd_id'       => $d['oem_order_hd_id'],
                            'product_customer_id'   => $d['product_customer_id'],
                            'oem_order_qty'         => $d['oem_order_qty'],
                            'due_date'              => $d['due_date'],
                            'loss_tolerance'        => $d['loss_tolerance'],
                            'updated_by'            => $d['updated_by']
                        ]);
                    }
                }

                if($data['deleted'] != null)
                {
                    $delete = array();
                    foreach($data['deleted'] as $del){
                        $delete[] = $del['oem_order_dt_id'];
                    }

                    DB::table('precise.oem_order_dt')
                    ->whereIn('oem_order_dt_id', $delete)
                    ->delete();
                }
                DB::commit();
                return response()->json(['status' => 'ok', 'message' => 'Purchase Order have been updated'], 200);
            }
            catch(\Exception $e){
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
    }

    public function destroy($id){
        DB::beginTransaction();
        try{
            $helper = new HelperController();
            $helper->reason("delete");

            DB::table('precise.oem_order_dt')
            ->where('oem_order_hd_id', $id)
            ->delete();

            DB::table('precise.oem_order_hd')
            ->where('oem_order_hd_id', $id)
            ->delete();

            DB::commit();
            return response()->json(['status' => 'ok', 'message' => 'Purchase Order have been deleted'], 200);
        }catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function destroy_po(Request $request){
        $validator = Validator::make(
            $request->all(),
            [
                'oem_order_number' => 'required',
                'customer_id' => 'required',
                'warehouse_id' => 'required'
            ]
        );
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            DB::beginTransaction();
            try{
                $helper = new HelperController();
                $helper->reason("delete");

                DB::table('precise.oem_order_dt as dt')
                ->where('oem_order_number', $request->oem_order_number)
                ->where('customer_id', $request->customer_id)
                ->where('warehouse_id', $request->warehouse_id)
                ->join('precise.oem_order_hd as hd', 'hd.oem_order_hd_id', '=', 'dt.oem_order_hd_id')
                ->delete();
                
                DB::table('precise.oem_order_hd')
                ->where('oem_order_number', $request->oem_order_number)
                ->where('customer_id', $request->customer_id)
                ->where('warehouse_id', $request->warehouse_id)
                ->delete();

                DB::commit();
                return response()->json(['status' => 'ok', 'message' => 'Purchase Order have been deleted'], 200);
            }catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
    }

    public function check(Request $request){
        $validator = Validator::make(
            $request->all(),
            [
                'oem_order_number' => 'required',
                'customer' => 'required',
                'warehouse' => 'required'
            ]
        );
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->checkPurchaseOrder = DB::table('precise.oem_order_hd')
                                        ->where('oem_order_number', $request->oem_order_number)
                                        ->where('customer_id', $request->customer_id)
                                        ->where('warehouse_id', $request->warehouse_id)
                                        ->select('oem_order_number')
                                        ->count();
            
            return response()->json(['status' => 'ok', 'message' => $this->checkPurchaseOrder], 200);
        }
    }

    public function joined(Request $request){
        $start = $request->get('start');
        $end = $request->get('end');
        $wh = $request->get('warehouse');
        $cust = $request->get('customer');

        $validator = Validator::make($request->all(), [
            'start' => 'required|date_format:Y-m-d|before_or_equal:end',
            'end' => 'required|date_format:Y-m-d|after_or_equal:start',
            'warehouse' => 'required',
            'customer' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $warehouse = explode('-', $wh);
            $customer = explode('-', $cust);

            $this->purchaseOrder = 
                    DB::table('precise.oem_order_hd as hd')
                    ->whereBetween('hd.oem_order_date', [$start, $end])
                    ->whereIn('hd.customer_id', $customer)
                    ->whereIn('hd.warehouse_id', $warehouse)
                    ->select(
                            'hd.oem_order_hd_id',
                            'hd.oem_order_number',
                            'hd.oem_order_date', 
                            'hd.customer_id',
                            'customer_code',
                            'customer_name', 
                            'hd.warehouse_id',
                            'wh.warehouse_code',
                            'wh.warehouse_name',
                            'hd.oem_order_description',
                            'oem_order_type_name', 
                            DB::raw("case hd.ppn_type
                                when 'I' then 'Include' 
                                when 'E' then 'Exclude' 
                                when 'N' then 'Non PPN' else 'Unknown'		
                            end ppn_type, 
                            case hd.oem_order_status 
                                when 'A' then 'Aktif'
                                when 'X' then 'Close'
                                when 'F' then 'Freeze'
                            end oem_order_status
                            "), 
                            'dt.oem_order_dt_seq', 
                            'prod.product_code',
                            'prod.product_name',
                            'dt.oem_order_dt_id',
                            'dt.oem_order_qty',  
                            'v.sum_delivery_qty',
                            'v.sum_on_going_qty',
                            'v.sum_received_qty',
                            'v.outstanding_qty',		            
                            'dt.due_date',
                            'dt.loss_tolerance',
                            'hd.created_on',
                            'hd.created_by',
                            'hd.updated_on',
                            'hd.updated_by'
                    )
                    ->leftJoin('precise.oem_order_dt as dt','hd.oem_order_hd_id','=','dt.oem_order_hd_id')
                    ->leftJoin('precise.customer as cust','hd.customer_id','=','cust.customer_id')
                    ->leftJoin('precise.oem_order_type as ot','hd.oem_order_type_id','=','ot.oem_order_type_id')
                    ->leftJoin('precise.product_customer as pc','dt.product_customer_id','=','pc.product_customer_id')
                    ->leftJoin('precise.product as prod','pc.product_id','=','prod.product_id')
                    ->leftJoin('precise.warehouse as wh','hd.warehouse_id','=','wh.warehouse_id')
                    ->leftJoin('precise.view_oem_outstanding_po as v', function($join){
                        $join->on('hd.oem_order_hd_id', '=', 'v.oem_order_hd_id')
                        ->on('dt.oem_order_dt_id', '=', 'v.oem_order_dt_id')
                        ->on('hd.warehouse_id','=','v.warehouse_id');
                    })
                    ->get();

                return response()->json(['data'=> $this->purchaseOrder], 200);
        }
    }

    public function remaining($id){
        $this->purchaseOrder = 
                DB::table('precise.view_oem_outstanding_po as v')
                ->where('v.oem_order_hd_id', $id)
                ->where('outstanding_qty', '>', 0)
                ->select(
                    'oem_order_dt_id',
                    'pc.product_id',
                    'product_code',
                    'product_name',
                    'oem_order_qty',
                    'v.sum_on_going_qty as total_on_going_qty',
                    'v.sum_received_qty as total_received_qty',
                    'v.outstanding_qty outstanding_qty',
                    '0 as delivery_qty',
                    'uom_code',
                    '0 as packaging_id',
                    'null as packaging_code',
                    'null as packaging_name',
                    '0 as packaging_qty',
                    'null as packaging_uom_code',
                    'null as packaging_description'
                )
                ->leftJoin('precise.product_customer as pc', 'v.product_customer_id','=','pc.product_customer_id')
                ->leftJoin('precise.product as p','pc.product_id', '=', 'p.product_id')
                ->get();
        return response()->json(['data'=> $this->purchaseOrder], 200);
    }

    public function outstanding_validating(Request $request){
        $product = $request->get('product');
        $customer = $request->get('customer');

        $validator = Validator::make($request->all(), [
            'product' => 'required',
            'customer' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->purchaseOrder = DB::table("precise.oem_order_hd as hd")
            ->where('hd.oem_order_status', '!=', 'X')
            ->where('hd.customer_id', $customer)
            ->where('pc.product_id', $product)
            ->groupBy('hd.oem_order_hd_id','hd.oem_order_number','hd.customer_id','dt.product_customer_id', 'dt.oem_order_qty')
            ->having('outstanding', '!=', 0)
            ->having('dt.oem_order_qty', '!=', 'outstanding')
            ->select(
                'hd.oem_order_hd_id',
                'hd.oem_order_number',
                'hd.customer_id',
                'pc.product_id',
                'dt.product_customer_id',
                'dt.oem_order_qty',
                DB::raw("
                SUM(IF(ods.is_delivery = 1, odd.delivery_qty, 0)) AS sum_delivery_qty,
                SUM(IF(ods.is_on_going = 1, odd.delivery_qty, 0)) AS sum_on_going_qty,
                SUM(IF(ods.is_received = 1, odd.received_qty, 0)) AS sum_received_qty,
                dt.oem_order_qty - SUM(IF(ods.is_on_going = 1, odd.delivery_qty, 0)) - SUM(IF(ods.is_received = 1, odd.received_qty, 0)) outstanding
                ")
            )
            ->leftJoin('precise.oem_order_dt as dt','hd.oem_order_hd_id','=','dt.oem_order_hd_id')
            ->leftJoin('precise.product_customer as pc','dt.product_customer_id','=','pc.product_customer_id')
            ->leftJoin('precise.oem_delivery_dt as odd','dt.oem_order_dt_id','=','odd.oem_order_dt_id')
            ->leftJoin('precise.oem_delivery_hd as odh','odd.oem_delivery_hd_id','=','odh.oem_delivery_hd_id')
            ->leftJoin('precise.oem_delivery_status as ods','odh.delivery_status','=','ods.oem_delivery_status_id')
            ->get();

            return response()->json(['data'=> $this->purchaseOrder], 200);
        }
    }

    public function outstanding_lookup(Request $request){
        $warehouse = $request->get('warehouse');
        $customer = $request->get('customer');
        $id = $request->get('id');

        $validator = Validator::make($request->all(), [
            'id'      => 'required',
            'product' => 'required',
            'customer'=> 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->purchaseOrder = DB::table('precise.view_oem_outstanding_po as v')
            ->where('v.customer_id', $customer)
            ->where('v.warehouse_id', $warehouse)
            ->where('outstanding_qty', '>', 0)
            ->orWhere('v.oem_order_hd_id', $id)
            ->select(
                'oem_order_hd_id', 
                'oem_order_number', 
                'oem_order_date',
                'pc.product_id', 
                'product_code',
                'product_name', 
                'oem_order_qty', 
                'v.sum_on_going_qty as total_on_going_qty',
                'v.sum_received_qty as total_received_qty',
                'v.outstanding_qty outstanding_qty',
                'due_date',
                'oem_order_description'
            )
            ->leftJoin('precise.product_customer as pc','v.product_customer_id','=','pc.product_customer_id')
            ->leftJoin('precise.product as p','pc.product_id','=','p.product_id')
            ->get();

            return response()->json(['data'=> $this->purchaseOrder], 200);
        }
    }

    public function outstanding_schedule(Request $request){
        $customer = $request->get('customer');
        $warehouse = $request->get('warehouse');
        $order_date = $request->get('order_date');
        $product_customer = $request->get('product_customer');

        $validator = Validator::make($request->all(), [
            'customer'  => 'required',
            'warehouse' => 'required',
            'order_date'=> 'required|date_format:Y-m-d',
            'product_customer' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $po = DB::table('precise.oem_order_hd as hd')
                ->where('hd.customer_id', $customer)
                ->where('hd.warehouse_id', $warehouse)
                ->where('hd.oem_order_date', $order_date)
                ->where('dt.product_customer_id', $product_customer)
                ->select(
                    'hd.oem_order_hd_id', 
                    'hd.oem_order_number',
                    'hd.oem_order_date',
                    'dt.due_date',
                    'hd.oem_order_status',
                    'dt.oem_order_dt_id',
                    'dt.oem_order_qty'
                )
                ->join('precise.oem_order_dt as dt', 'hd.oem_order_hd_id', '=', 'dt.oem_order_hd_id');
            
            $delivery = DB::table('precise.oem_order_hd as hd')
                ->where('hd.customer_id', $customer)
                ->where('hd.warehouse_id', $warehouse)
                ->where('hd.oem_order_date', $order_date)
                ->where('odh.oem_delivery_date', $order_date)
                ->where('dt.product_customer_id', $product_customer)
                ->select(
                    'hd.oem_order_hd_id',
                    'dt.oem_order_dt_id',
                    'dt.oem_order_qty',
                    DB::raw("
                        sum(if(ods.is_delivery = 1, odd.`delivery_qty`, 0)) as sum_delivery_qty,
                        sum(if(ods.is_on_going = 1, odd.delivery_qty, 0)) as sum_on_going_qty,
                        sum(if(ods.is_received = 1, odd.received_qty, 0)) as sum_received_qty		     
                    ")
                )
                ->join('precise.oem_order_dt as dt','hd.oem_order_hd_id','=','dt.oem_order_hd_id')
                ->leftJoin('precise.oem_delivery_dt as odd','dt.oem_order_dt_id','=','odd.oem_order_dt_id')
                ->join('precise.oem_delivery_hd as odh','odd.oem_delivery_hd_id', '=', 'odh.oem_delivery_hd_id')
                ->leftJoin('precise.oem_delivery_status as ods', 'odh.delivery_status', '=', 'ods.oem_delivery_status_id')
                ->groupBy('hd.oem_order_hd_id','dt.oem_order_dt_id');

            $this->purchaseOrder = DB::table(DB::raw("({$po->toSql()})po"))
            ->mergeBindings($po)
            ->mergeBindings($delivery)
            ->where(DB::raw("po.`oem_order_qty` - ifnull(sum_on_going_qty, 0) - ifnull(sum_received_qty, 0)"), '>', 0)
            ->select(
                'po.oem_order_hd_id',
                'po.oem_order_number',
                'po.oem_order_date',
                'po.due_date',
                'po.oem_order_status',
                'po.oem_order_dt_id',
                'po.oem_order_qty',
                DB::raw("
                    ifnull(sum_delivery_qty, 0) as total_delivery_qty,
                    ifnull(sum_on_going_qty, 0) as total_on_going_qty,
                    ifnull(sum_received_qty,0) as total_received_qty,
                    po.`oem_order_qty` - ifnull(sum_on_going_qty, 0) - ifnull(sum_received_qty, 0) as outstanding_qty
                ")
            )
            ->leftJoin(DB::raw("({$delivery->toSql()})delivery"), function($join){
                $join->on('po.oem_order_hd_id', '=', 'delivery.oem_order_hd_id')
                ->on('po.oem_order_dt_id', '=', 'delivery.oem_order_dt_id');
            })
            ->get();

            return response()->json(['data'=> $this->purchaseOrder], 200);
        }
    }
}
