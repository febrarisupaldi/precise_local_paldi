<?php

namespace App\Http\Controllers\Api\OEM;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\Master\HelperController;
use App\Http\Controllers\Api\Helpers\QueryController;

class DeliveryScheduleController extends Controller
{
    private $deliverySchedule;
    public function index(Request $request){
        $start      = $request->get('start');
        $end        = $request->get('end');
        $warehouse  = $request->get('warehouse');
        $customer   = $request->get('customer');

        
        $validator = Validator::make($request->all(), [
            'start'     => 'required|date_format:Y-m-d|before_or_equal:end',
            'end'       => 'required|date_format:Y-m-d|after_or_equal:start',
            'warehouse' => 'required',
            'customer'  => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $customer = str_replace('-',',',$customer);
            $this->deliverySchedule = DB::select
                (
                    'call precise.oem_get_delivery_schedule(:start,:end,:warehouse,:customer)', 
                    [
                        'start'     => $start, 
                        'end'       => $end,
                        'warehouse' => $warehouse,
                        'customer'  => $customer
                    ]
                );
                
            return response()->json(['data'=>$this->deliverySchedule], 200);
            
        }
    }

    public function show(Request $request){
        $product_customer   = $request->get('product_customer');
        $warehouse          = $request->get('warehouse');
        $date               = $request->get('schedule_date');

        
        $validator = Validator::make($request->all(), [
            'schedule_date'     => 'required|date_format:Y-m-d',
            'warehouse'         => 'required',
            'product_customer'  => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->deliverySchedule = DB::table('precise.oem_delivery_schedule as ods')
                ->where('ods.product_customer_id', $product_customer)
                ->where('ods.warehouse_id', $warehouse)
                ->where('schedule_date', $date)
                ->select(
                    'oem_delivery_schedule_id',
                    'ods.product_customer_id',
                    'pc.product_id',
                    'p.product_code',
                    'p.product_name',
                    'ods.warehouse_id',
                    'w.warehouse_code',
                    'w.warehouse_name',
                    'pc.customer_id',
                    'c.customer_code',
                    'c.customer_name',
                    'schedule_date',
                    'schedule_qty'
                )
                ->leftJoin('precise.product_customer as pc','ods.product_customer_id','=','pc.product_customer_id')
                ->leftJoin('precise.product as p','pc.product_id','=','p.product_id')
                ->leftJoin('precise.warehouse as w','ods.warehouse_id','=','w.warehouse_id')
                ->leftJoin('precise.customer as c','pc.customer_id','=','c.customer_id')
                ->get();
            
            return response()->json(['data'=>$this->deliverySchedule], 200);
        }
    }

    public function check(Request $request){
        $product_customer   = $request->get('product_customer');
        $warehouse          = $request->get('warehouse');
        $date               = $request->get('schedule_date');

        
        $validator = Validator::make($request->all(), [
            'schedule_date'     => 'required|date_format:Y-m-d',
            'warehouse'         => 'required',
            'product_customer'  => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->deliverySchedule = DB::table('precise.oem_delivery_schedule as')
                ->where('product_customer_id', $product_customer)
                ->where('warehouse_id', $warehouse)
                ->where('schedule_date', $date)
                ->select(
                    "product_customer_id"
                )
                ->count();

            return response()->json(['status' => 'ok', 'message' => $this->deliverySchedule], 200);
        }
    }

    public function create(Request $request){
        try{
            $data = $request->json()->all();
            foreach($data as $d){
                $dt[] = [
                    'product_customer_id'   => $d['product_customer_id'],
                    'warehouse_id'          => $d['warehouse_id'],
                    'schedule_date'         => $d['schedule_date'],
                    'schedule_qty'          => $d['schedule_qty'],
                    'created_by'            => $d['created_by']
                ];
            }

            $this->deliverySchedule = DB::table('precise.oem_delivery_schedule')
                ->insert($dt);
            if($this->deliverySchedule > 0)
            {
                return response()->json(['status' => 'ok', 'message' => 'Delivery Schedule berhasil diinput']);
            }else{
                return response()->json(['status' => 'error', 'message' => 'Ada Permasalahan Saat Input Delivery Schedule']);
            }
        }catch(\Exception $e){
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function update(Request $request){
        $validator = Validator::make($request->all(), [
            'schedule_qty'              => 'required',
            'updated_by'                => 'required',
            'oem_delivery_schedule_id'  => 'required|exists:oem_delivery_schedule, oem_delivery_schedule_id'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            try{
                DB::beginTransaction();
                $helper = new HelperController();
                $helper->reason("update");

                $this->deliverySchedule = DB::table("precise.oem_delivery_schedule")
                    ->where('oem_delivery_schedule_id', $request->oem_delivery_schedule_id)
                    ->update([
                        'schedule_qty'  => $request->schedule_qty,
                        'updated_by'    => $request->updated_by
                    ]);

                if ($this->deliverySchedule == 0) {
                    DB::rollback();
                    return response()->json(['status' => 'error', 'message' => 'Failed to update delivery schedule, Contact your administrator']);
                } else {
                    DB::commit();
                    return response()->json(['status' => 'ok', 'message' => 'Delivery schedule has been updated']);
                }
            }catch(\Exception $e){
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
    }

    public function destroy(Request $request){
    
        $data = $request->json()->all();

        $validator = Validator::make(json_decode(json_encode($data),true), [
            'schedule_date'     => 'required|date_format:Y-m-d',
            'warehouse'         => 'required',
            'product_customer'  => 'required'
        ]);
        DB::beginTransaction();
        try{
            QueryController::reason($data);
            $this->deliverySchedule = DB::table("precise.oem_delivery_schedule")
                ->where("warehouse_id", $data["warehouse_id"])
                ->where("schedule_date", $data["schedule_date"])
                ->where("product_customer", $data["product_customer_id"])
                ->delete();

            DB::commit();
            return response()->json(['status' => 'ok', 'message' => 'Delivery Schedule have been deleted'], 200);
        }catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
