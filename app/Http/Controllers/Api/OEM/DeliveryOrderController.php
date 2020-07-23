<?php

namespace App\Http\Controllers\Api\OEM;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\Helpers\QueryController;


class DeliveryOrderController extends Controller
{
    private $deliveryOrder, $checkDeliveryOrder;
    public function index(Request $request){
        $start = $request->get('start');
        $end = $request->get('end');
        $wh = $request->get('warehouse');
        $validator = Validator::make($request->all(), [
            'start' => 'required|date_format:Y-m-d|before_or_equal:end',
            'end' => 'required|date_format:Y-m-d|after_or_equal:start',
            'warehouse' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $warehouse = explode("-", $wh);
            $this->deliveryOrder = DB::table("precise.oem_delivery_hd as hd")
                ->whereBetween('hd.oem_delivery_date', [$start, $end])
                ->whereIn('hd.warehouse_id', $warehouse)
                ->select(
                    'hd.oem_delivery_hd_id',
                    'oem_delivery_number',
                    'oem_delivery_date',
                    'hd.oem_order_hd_id',
                    'hd.warehouse_id',
                    'wh.warehouse_code',
                    'wh.warehouse_name',
                    'hd.delivery_method_id',
                    'dm.delivery_method_name',
                    'hd.customer_id',
                    'cust.customer_code',
                    'cust.customer_name',
                    'ca.address',
                    'POHd.oem_order_number', 
                    'hd.vehicle_id',
                    'v.license_number',
                    'v.vehicle_model',
                    'hd.driver_nik',
                    'e.employee_name as driver_name',
                    'hd.taker_name',
                    'hd.delivery_description', 
                    'hd.delivery_status',
                    DB::raw("concat(ods.`oem_delivery_status_code`, ' - ', ods.`oem_delivery_status_name`) as delivery_status_code_and_name"),
                    'hd.received_date',
                    'hd.print_count',
                    'hd.created_on',
                    'hd.created_by',
                    'hd.updated_on',
                    'hd.updated_by'
                )
                ->leftJoin('precise.oem_order_hd as POHd','hd.oem_order_hd_id','=','POHd.oem_order_hd_id')
                ->leftJoin('precise.customer as cust','hd.customer_id','=','cust.customer_id')
                ->leftJoin('precise.warehouse as wh','hd.warehouse_id','=','wh.warehouse_id')
                ->leftJoin('precise.vehicle as v','hd.vehicle_id','=','v.vehicle_id')
                ->leftJoin('precise.employee as e','hd.driver_nik','=','e.employee_nik')
                ->leftJoin('precise.delivery_method as dm','hd.delivery_method_id','=','dm.delivery_method_id')
                ->leftJoin('precise.oem_delivery_status as ods','hd.delivery_status','=','ods.oem_delivery_status_id')
                ->leftJoin('precise.customer_address as ca','POHd.shipping_address_id','=','ca.customer_address_id')
                ->get();

            return response()->json(["data"=> $this->deliveryOrder], 200);
        }
    }
    public function show($id)
    {
        try{
            $master = DB::table('precise.oem_delivery_hd as hd')
                ->where('hd.oem_delivery_hd_id', $id)
                ->select(
                    'hd.oem_delivery_hd_id',
                    'hd.oem_delivery_number',
                    'hd.oem_delivery_date',
                    'hd.oem_order_hd_id',
                    'poHd.oem_order_number',
                    'hd.customer_id',
                    'customer_code',
                    'customer_name',
                    'hd.warehouse_id',
                    'warehouse_code',
                    'warehouse_name',
                    'hd.vehicle_id',
                    'vehicle_model',
                    'license_number',
                    'hd.driver_nik',
                    'e.employee_name',
                    'delivery_description',
                    'delivery_status',
                    'received_date',
                    'print_count',
                    'hd.created_by',
                    'hd.created_on',
                    'hd.updated_by',
                    'hd.updated_on'
                )
                ->leftJoin('precise.oem_order_hd as poHd', 'hd.oem_order_hd_id', '=', 'poHd.oem_order_hd_id')
                ->leftJoin('precise.customer as cust', 'hd.customer_id', '=', 'cust.customer_id')
                ->leftJoin('precise.warehouse as wh', 'hd.warehouse_id', '=', 'wh.warehouse_id')
                ->leftJoin('precise.vehicle as v', 'hd.vehicle_id', '=', 'v.vehicle_id')
                ->leftJoin('precise.employee as e', 'hd.driver_nik', '=', 'employee_nik')
                ->first();
            
                DB::statement('set @ctr = 0;');
                $detail = DB::table('precise.oem_delivery_dt as dt')
                    ->where('dt.oem_delivery_hd_id', $master->oem_delivery_hd_id)
                    ->select(
                        DB::raw("@ctr:=@ctr + 1 as 'seq'"),
                        'dt.oem_delivery_dt_id',
                        'dt.oem_order_dt_id',
                        'dt.product_id',
                        'prod.product_code as Kode barang',
                        'prod.product_name as Nama barang',
                        'poDt.oem_order_qty as Qty order',
                        DB::raw(
                            "
                            ifnull(dt.delivery_qty, 0) as 'Qty terkirim',
                            poDt.oem_order_qty - ifnull(dt.delivery_qty, 0) as 'Qty outstanding'"
                        ),
                        'dt.delivery_qty as Qty pengiriman',
                        'dt.uom_code as UOM',
                        'dt.received_qty as Qty penerimaan',
                        'dt.uom_code_received as UOM terima'
                    )
                    ->leftJoin('precise.oem_order_dt as poDt', 'dt.oem_order_dt_id', '=', 'poDt.oem_order_dt_id')
                    ->leftJoin('precise.product as prod', 'dt.product_id', '=', 'prod.product_id')
                    ->get();

                $this->deliveryOrder =
                    array(
                        "oem_delivery_hd_id"    => $master->oem_delivery_hd_id,
                        "oem_delivery_number"   => $master->oem_delivery_number,
                        "oem_delivery_date"     => $master->oem_delivery_date,
                        "oem_order_hd_id"       => $master->oem_order_hd_id,
                        "oem_order_number"      => $master->oem_order_number,
                        "customer_id"           => $master->customer_id,
                        "customer_code"         => $master->customer_code,
                        "customer_name"         => $master->customer_name,
                        "warehouse_id"          => $master->warehouse_id,
                        "warehouse_code"        => $master->warehouse_code,
                        "warehouse_name"        => $master->warehouse_name,
                        "vehicle_id"            => $master->vehicle_id,
                        "vehicle_model"         => $master->vehicle_model,
                        "license_number"        => $master->license_number,
                        "driver_nik"            => $master->driver_nik,
                        "employee_name"         => $master->employee_name,
                        "delivery_description"  => $master->delivery_description,
                        "delivery_status"       => $master->delivery_status,
                        "received_date"         => $master->received_date,
                        "print_count"           => $master->print_count,
                        "created_by"            => $master->created_by,
                        "created_on"            => $master->created_on,
                        "updated_by"            => $master->updated_by,
                        "updated_on"            => $master->updated_on,
                        "detail"                => $detail
                    );

            return response()->json($this->deliveryOrder, 200);
        }catch(\Exception $e){
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function show_by_po($id){
        $this->deliveryOrder = DB::table('precise.oem_delivery_hd as hd')
            ->where('hd.oem_order_hd_id', $id)
            ->select(
                'oem_delivery_hd_id',
                'oem_delivery_number',
                'oem_delivery_date',
                DB::raw("
                    concat(warehouse_code, ' - ', warehouse_name) as warehouse_code_and_name
                ")
            )
            ->leftJoin('precise.warehouse as wh', 'hd.warehouse_id', '=', 'wh.warehouse_id')
            ->get();

        return response()->json(["data"=> $this->deliveryOrder], 200);
    }

    public function check(Request $request){
        $stat = $request->get('status');
        $po = $request->get('purchase_order');
        $validator = Validator::make(
            $request->all(),
            [
                'purchase_order'=> 'required',
                'status'        => 'required'
            ]
        );
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $status = explode("-", $stat);
            $this->deliveryOrder = DB::table('precise.oem_delivery_hd')
                ->whereIn('delivery_status', $status)
                ->where('oem_order_hd_id', $po)
                ->select('oem_delivery_hd_id')
                ->count();

            return response()->json(['status' => 'ok', 'message' => $this->deliveryOrder], 200);
        }
    }

    public function schedule(Request $request){
        $product  = $request->get('product');
        $customer = $request->get('customer');
        $warehouse= $request->get('warehouse');
        $date     = $request->get('delivery_date');

        $validator = Validator::make(
            $request->all(),
            [
                'product'       => 'required',
                'customer'      => 'required',
                'warehouse'     => 'required',
                'delivery_date' => 'required'
            ]
        );
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->deliveryOrder = DB::table('precise.oem_delivery_hd as hd')
                ->where('hd.customer_id', $customer)
                ->where('hd.warehouse_id', $warehouse)
                ->where('dt.product_id', $product)
                ->where('hd.oem_delivery_date', $date)
                ->select(
                    'hd.oem_delivery_hd_id',
                    'hd.oem_delivery_number',
                    'hd.oem_delivery_date',
                    DB::raw("
                        concat(ods.oem_delivery_status_code, ' - ', ods.oem_delivery_status_name) as delivery_status
                    "),
                    'dt.delivery_qty',
                    'dt.received_qty',
                    'dt.uom_code',
                    'ooh.oem_order_number',
                    'ooh.oem_order_date'
                )
                ->join('precise.oem_delivery_dt as dt','hd.oem_delivery_hd_id','=','dt.oem_delivery_hd_id')
                ->leftJoin('precise.oem_order_dt as ood','dt.oem_order_dt_id','=','ood.oem_order_dt_id')
                ->join('precise.oem_order_hd as ooh','ood.oem_order_hd_id','=','ooh.oem_order_hd_id')
                ->leftJoin('precise.oem_delivery_status as ods','hd.delivery_status','=','ods.oem_delivery_status_id');

            return response()->json(["data"=> $this->deliveryOrder], 200);
        }
    }

    public function history(Request $request){
        $id     = $request->get('id');
        $detail = $request->get('detail');
        $date   = $request->get('delivery_date');

        $validator = Validator::make(
            $request->all(),
            [
                'id'            => 'required',
                'detail'        => 'required',
                'delivery_date' => 'required'
            ]
        );
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->deliveryOrder = DB::table("precise.oem_delivery_hd as hd")
                ->where('hd.oem_order_hd_id', $id)
                ->where('dt.oem_order_dt_id', $detail)
                ->where('hd.oem_delivery_date', $date)
                ->select(
                   'hd.oem_delivery_hd_id',
                   'hd.oem_delivery_number',
                   'hd.oem_delivery_date',
                   DB::row("
                        concat(ods.oem_delivery_status_code, ' - ', ods.oem_delivery_status_name) as delivery_status
                   "),
                   'dt.delivery_qty',
                   'dt.received_qty',
                   'dt.uom_code'
                )
                ->join('precise.oem_delivery_dt as dt','hd.oem_delivery_hd_id','=','dt.oem_delivery_hd_id')
                ->leftJoin('precise.oem_delivery_status as ods','hd.delivery_status','=','ods.oem_delivery_status_id')
                ->get();

            return response()->json(["data"=> $this->deliveryOrder], 200);
        }
    }

    public function show_history($id){
        $this->deliveryOrder = DB::table('precise.oem_delivery_hd as hd')
            ->where('hd.oem_order_hd_id', $id)
            ->select(
                'hd.oem_delivery_hd_id',
                'oem_delivery_number',
                'hd.delivery_status',
                DB::raw(" 
                    concat(ods.`oem_delivery_status_code`, ' - ', ods.`oem_delivery_status_name`) as delivery_status_code_and_name
                "),
                'ood.due_date',
                'ood.oem_order_qty',
                'oem_delivery_date',
                'received_date', 
                'dt.product_id',
                'p.product_code',
                'p.product_name',
                'dt.uom_code',
                'delivery_qty',
                'received_qty'
            )
            ->join('precise.oem_delivery_dt as dt','hd.oem_delivery_hd_id','=','dt.oem_delivery_hd_id')
            ->leftJoin('precise.oem_order_dt as ood','dt.oem_order_dt_id','=','ood.oem_order_dt_id')
            ->leftJoin('precise.product as p','dt.product_id','=','p.product_id')
            ->leftJoin('precise.oem_delivery_status as ods','hd.delivery_status','=','ods.oem_delivery_status_id')
            ->get();

        return response()->json($this->deliveryOrder, 200);
    }

    public function create(Request $request){
        $data = $request->json()->all();
        $validator = Validator::make(json_decode(json_encode($data),true),[
            'oem_delivery_date'  => 'required|date_format:Y-m-d',
            'oem_order_hd_id'    => 'required|exists:oem_order_hd, oem_order_hd_id',
            'customer_id'        => 'required|exists:customer,customer_id',
            'warehouse_id'       => 'required|exists:warehouse, warehouse_id',
            'delivery_method_id' => 'required|exists:delivery_method, delivery_method_id',
            'vehicle_id'         => 'nullable|exists:vehicle, vehicle_id',
            'driver_nik'         => 'nullable|exists:driver, driver_nik',
            'delivery_status'    => 'required|exists: oem_delivery_status, oem_delivery_status_id',
            'created_by'         => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            DB::beginTransaction();
            try
            {
                
                $id = DB::table('precise.oem_delivery_hd')
                ->insertGetId([
                    'oem_delivery_date'     => $data['oem_delivery_date'],
                    'oem_order_hd_id'       => $data['oem_order_hd_id'],
                    'customer_id'           => $data['customer_id'],
                    'warehouse_id'          => $data['warehouse_id'],
                    'delivery_method_id'    => $data['delivery_method_id'],
                    'vehicle_id'            => $data['vehicle_id'],
                    'driver_nik'            => $data['driver_nik'],
                    'taker_name'            => $data['taker_name'],
                    'delivery_description'  => $data['delivery_description'],
                    'delivery_status'       => $data['delivery_status'],
                    'created_by'            => $data['created_by']
                ]);

                foreach($data['detail'] as $d){
                    $dt[] = [
                        'oem_delivery_hd_id'    => $id,
                        'oem_order_dt_id'       => $d['oem_order_dt_id'],
                        'product_id'            => $d['product_id'],
                        'delivery_qty'          => $d['delivery_qty'],
                        'uom_code'              => $d['uom_code'],
                        'uom_code_received'     => $d['uom_code_received'],
                        'packaging_id'          => $d['packaging_id'],
                        'packaging_qty'         => $d['packaging_qty'],
                        'packaging_description' => $d['packaging_description'],
                        'created_by'            => $d['created_by']
                    ];
                }

                DB::table('precise.oem_delivery_dt')
                ->insert($dt);

                $trans = DB::table('precise.oem_delivery_hd')
                        ->where('oem_delivery_hd_id', $id)
                        ->select('oem_delivery_number')
                        ->first();

                DB::commit();
                return response()->json(['status' => 'ok', 'message' => $trans->oem_delivery_number], 200);
            }
            catch(\Exception $e){
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
    }

    public function update(Request $request){
        $data = $request->json()->all();
        $validator = Validator::make(json_decode(json_encode($data),true),[
            'oem_delivery_date'  => 'required|date_format:Y-m-d',
            'oem_order_hd_id'    => 'required|exists:oem_order_hd, oem_order_hd_id',
            'customer_id'        => 'required|exists:customer,customer_id',
            'warehouse_id'       => 'required|exists:warehouse, warehouse_id',
            'delivery_method_id' => 'required|exists:delivery_method, delivery_method_id',
            'vehicle_id'         => 'nullable|exists:vehicle, vehicle_id',
            'driver_nik'         => 'nullable|exists:driver, driver_nik',
            'delivery_status'    => 'required|exists: oem_delivery_status, oem_delivery_status_id',
            'updated_by'         => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            DB::beginTransaction();
            try
            {
                QueryController::reason($data);
                DB::table("precise.oem_delivery_hd")
                ->where("oem_delivery_hd_id",$data['oem_delivery_hd_id'])
                ->update([
                    'oem_delivery_date'     => $data['oem_delivery_date'],
                    'oem_order_hd_id'       => $data['oem_order_hd_id'],
                    'customer_id'           => $data['customer_id'],
                    'warehouse_id'          => $data['warehouse_id'],
                    'delivery_method_id'    => $data['delivery_method_id'],
                    'vehicle_id'            => $data['vehicle_id'],
                    'driver_nik'            => $data['driver_nik'],
                    'taker_name'            => $data['taker_name'],
                    'delivery_description'  => $data['delivery_description'],
                    'updated_by'            => $data['updated_by']
                ]);

                if($data['inserted'] != null)
                {
                    foreach($data['inserted'] as $d)
                    {
                        $dt[] = [
                            'oem_delivery_hd_id'    => $d['oem_delivery_hd_id'],
                            'oem_order_dt_id'       => $d['oem_order_dt_id'],
                            'product_id'            => $d['product_id'],
                            'delivery_qty'          => $d['delivery_qty'],
                            'uom_code'              => $d['uom_code'],
                            'uom_code_received'     => $d['uom_code_received'],
                            'packaging_id'          => $d['packaging_id'],
                            'packaging_qty'         => $d['packaging_qty'],
                            'packaging_description' => $d['packaging_description'],
                            'created_by'            => $d['created_by']
                        ];
                    }

                    DB::table('precise.oem_delivery_dt')
                    ->insert($dt);

                    if($data['updated'] != null)
                    {
                        foreach($data['updated'] as $d)
                        {
                            DB::table("precise.oem_delivery_dt")
                            ->where("oem_delivery_dt_id",$d['oem_delivery_dt_id'])
                            ->update([
                                'delivery_qty'          => $d['delivery_qty'],
                                'packaging_id'          => $d['packaging_id'],
                                'packaging_qty'         => $d['packaging_qty'],
                                'packaging_description' => $d['packaging_description'],
                                'updated_by'            => $d['updated_by']
                            ]);
                        }
                    }

                    if($data['deleted'] != null)
                    {
                        $delete = array();
                        foreach($data['deleted'] as $del){
                            $delete[] = $del['oem_delivery_dt_id'];
                        }

                        DB::table('precise.oem_delivery_dt')
                        ->whereIn('oem_delivery_dt_id', $delete)
                        ->delete();
                    }

                    DB::commit();
                    return response()->json(['status' => 'ok', 'message' => 'Delivery Order have been updated'], 200);
                }
            }
            catch(\Exception $e){
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try{
            $helper = new HelperController();
            $helper->reason("delete");

            DB::table('precise.oem_delivery_dt')
            ->where('oem_delivery_hd_id', $id)
            ->delete();

            DB::table('precise.oem_delivery_hd')
            ->where('oem_delivery_hd_id', $id)
            ->delete();

            DB::commit();
            return response()->json(['status' => 'ok', 'message' => 'Delivery Order have been deleted'], 200);
        }catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function joined(Request $request)
    {
        $start = $request->get('start');
        $end = $request->get('end');
        $wh = $request->get('warehouse');

        $validator = Validator::make($request->all(), [
            'start' => 'required|date_format:Y-m-d|before_or_equal:end',
            'end' => 'required|date_format:Y-m-d|after_or_equal:start',
            'warehouse' => 'required'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            try{
                $warehouse = explode('-', $wh);
                $query = DB::table('oem_delivery_hd as hd')
                ->whereBetween('hd.oem_delivery_date', [$start, $end])
                ->whereIn('hd.warehouse_id', $warehouse)
                ->select(
                    'hd.oem_delivery_hd_id',
                    'oem_delivery_number',
                    'oem_delivery_date',
                    'hd.oem_order_hd_id',
                    'hd.warehouse_id',
                    'wh.warehouse_code',
                    'wh.warehouse_name',
                    'hd.delivery_method_id',
                    'dm.delivery_method_name',
                    'hd.customer_id',
                    'cust.customer_code',
                    'cust.customer_name',
                    'POHd.oem_order_number', 
                    'hd.vehicle_id',
                    'v.license_number',
                    'v.vehicle_model',
                    'hd.driver_nik',
                    'e.employee_name as driver_name',
                    'hd.taker_name',
                    'hd.delivery_description', 
                    'hd.delivery_status',
                    DB::raw("
                        concat(ods.`oem_delivery_status_code`, ' - ', ods.`oem_delivery_status_name`) as delivery_status_code_and_name
                    "),
                    'hd.received_date',
                    'hd.print_count',
                    'prod.product_code',
                    'prod.product_name',
                    'dt.delivery_qty',
                    'dt.uom_code', 
                    'dt.received_qty',
                    'dt.uom_code_received',
                    'hd.created_on',
                    'hd.created_by',
                    'hd.updated_on',
                    'hd.updated_by'
                )
                ->leftJoin('precise.oem_delivery_dt as dt','hd.oem_delivery_hd_id','=','dt.oem_delivery_hd_id')
                ->leftJoin('precise.oem_order_hd as POHd','hd.oem_order_hd_id','=','POHd.oem_order_hd_id')
                ->leftJoin('precise.customer as cust','hd.customer_id','=','cust.customer_id')
                ->leftJoin('precise.warehouse as wh','hd.warehouse_id','=','wh.warehouse_id')
                ->leftJoin('precise.vehicle as v','hd.vehicle_id','=','v.vehicle_id')
                ->leftJoin('precise.employee as e','hd.driver_nik','=','e.employee_nik')
                ->leftJoin('precise.product as prod','dt.product_id','=','prod.product_id')
                ->leftJoin('precise.delivery_method as dm','hd.delivery_method_id','=','dm.delivery_method_id')
                ->leftJoin('precise.oem_delivery_status as ods','hd.delivery_status','=','ods.oem_delivery_status_id');

                $this->deliveryOrder = DB::table('precise.oem_delivery_hd as hd')
                    ->whereBetween('hd.oem_delivery_date', [$start, $end])
                    ->whereIn('hd.warehouse_id', $warehouse)
                    ->whereNotNull('odp.packaging_id')
                    ->select(
                        'hd.oem_delivery_hd_id',
                        'oem_delivery_number',
                        'oem_delivery_date',
                        'hd.oem_order_hd_id',
                        'hd.warehouse_id',
                        'wh.warehouse_code',
                        'wh.warehouse_name',
                        'hd.delivery_method_id',
                        'dm.delivery_method_name',
                        'hd.customer_id',
                        'cust.customer_code',
                        'cust.customer_name',
                        'POHd.oem_order_number', 
                        'hd.vehicle_id',
                        'v.license_number',
                        'v.vehicle_model',
                        'hd.driver_nik',
                        'e.employee_name as driver_name',
                        'hd.taker_name',
                        'hd.delivery_description', 
                        'hd.delivery_status',
                        DB::raw("
                            concat(ods.`oem_delivery_status_code`, ' - ', ods.`oem_delivery_status_name`) as delivery_status_code_and_name
                        "),
                        'hd.received_date',
                        'hd.print_count',
                        'prod.product_code',
                        'prod.product_name',
                        'odp.packaging_qty',
                        'prod.uom_code',
                        'odp.packaging_qty',
                        'prod.uom_code',
                        'hd.created_on',
                        'hd.created_by',
                        'hd.updated_on',
                        'hd.updated_by'
                    )
                    ->leftJoin('precise.oem_delivery_packaging as odp','hd.oem_delivery_hd_id','=','odp.oem_delivery_hd_id')
                    ->leftJoin('precise.oem_order_hd as POHd','hd.oem_order_hd_id','=','POHd.oem_order_hd_id')
                    ->leftJoin('precise.customer as cust','hd.customer_id','=','cust.customer_id')
                    ->leftJoin('precise.warehouse as wh','hd.warehouse_id','=','wh.warehouse_id')
                    ->leftJoin('precise.vehicle as v','hd.vehicle_id','=','v.vehicle_id')
                    ->leftJoin('precise.employee as e','hd.driver_nik','=','e.employee_nik')
                    ->leftJoin('precise.product as prod','odp.packaging_id','=','prod.product_id')
                    ->leftJoin('precise.delivery_method as dm','hd.delivery_method_id','=','dm.delivery_method_id')
                    ->leftJoin('precise.oem_delivery_status as ods','hd.delivery_status','=','ods.oem_delivery_status_id')
                    ->union($query)
                    ->orderBy('oem_delivery_number')
                    ->orderByDesc('product_code')
                    ->get();

                return response()->json(["data"=> $this->deliveryOrder], 200);
            }catch(\Exception $e){
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
    }
}
