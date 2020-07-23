<?php

namespace App\Http\Controllers\Api\PPIC;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\Helpers\QueryController;

class WorkOrderController extends Controller
{
    private $workOrder;
    
    public function index(Request $request){
        $start = $request->get('start');
        $end = $request->get('end');
        $wc = $request->get('workcenter');
        $validator = Validator::make($request->all(), [
            'start'     => 'required|date_format:Y-m-d|before_or_equal:end',
            'end'       => 'required|date_format:Y-m-d|after_or_equal:start',
            'workcenter'=> 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $workcenter = explode("-", $wc);
            $this->workOrder = DB::table('precise.work_order as wo')
                ->whereIn('wo.workcenter_id',[$workcenter])
                ->whereBetween('wo.start_date', [$start, $end])
                ->select(
                    'wo.work_order_hd_id',
                    'wo.work_order_number', 
                    'w.workcenter_code',
                    'w.workcenter_name', 
                    'p.product_code',
                    'p.product_name',
                    'wo.work_order_qty',
                    'bom.bom_code', 
                    'bom.bom_name',
                    'wo.start_date',
                    'wo.est_finish_date',
                    'wo.work_order_description', 
                    'wo.work_order_type',
                    'wo.work_order_status',
                    'wo.created_on',
                    'wo.created_by',
                    'wo.updated_on',
                    'wo.updated_by'
                )->leftJoin('precise.product as p','wo.product_id','=','')
                ->leftJoin('precise.workcenter as w','wo.workcenter_id','=','w.workcenter_id')
                ->leftJoin('precise.bom_hd as bom','wo.bom_default','=','bom.bom_hd_id')
                ->get();

            return response()->json(["data" => $this->workOrder]);
        }
    }

    public function show($id){
        $this->workOrder = DB::table("precise.work_order as wo")
            ->where("wo.work_order_hd_id", $id)
            ->select(
                'wo.work_order_hd_id',
                'wo.work_order_number',
                'wo.workcenter_id',
                'wo.product_id',
                'wo.bom_default', 
                'w.workcenter_code',
                'w.workcenter_name', 
                'p.product_code',
                'p.product_name',
                'wo.work_order_qty',
                'bom.bom_code', 
                'bom.bom_name',
                'wo.start_date',
                'wo.est_finish_date',
                'wo.work_order_description', 
                'wo.work_order_type',
                'wo.work_order_status'
            )
            ->leftJoin("precise.product as p","wo.product_id","=","p.product_id")
            ->leftJoin("precise.workcenter as w","wo.workcenter_id","=","w.workcenter_id")
            ->leftJoin("precise.bom_hd as bom","wo.bom_default","=","bom.bom_hd_id")
            ->first();
        
        return response()->json($this->workOrder);
    }

    public function showByWorkcenter($id){
        $this->workOrder = DB::table("precise.work_order as wo")
            ->where("wo.workcenter_id", $id)
            ->select(
                'wo.work_order_hd_id',
                'wo.work_order_number',
                'p.product_code',
                'p.product_name',
                'wo.work_order_qty'
            )
            ->leftJoin('precise.product as p', 'wo.product_id', '=', 'p.product_id')
            ->get();

        return response()->json(["data" => $this->workOrder]);
    }

    public function create(Request $request){
        $validator = Validator::make($request->all(), [
            'work_order_number'     =>'required|unique:work_order,work_order_number',
            'workcenter_id'         =>'required|exists:workcenter,workcenter_id',
            'product_id'            =>'required|exists:product,product_id',
            'work_order_qty'        =>'required|numeric',
            'bom_default'           =>'required|exists:bom_hd,bom_hd_id',
            'start_date'            =>'required|date_format:Y-m-d|before_or_equal:est_finish_date',
            'est_finish_date'       =>'required|date_format:Y-m-d|after_or_equal:start_date',
            'parent_work_order_id'  =>'nullable|exists:work_order,work_order_hd_id',
            'work_order_type'       =>'required|exists:work_order_type,work_order_type_code',
            'work_order_status'     =>'required|exists:work_order_status,work_order_status_code',
            'created_by'            =>'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->workOrder = DB::table("precise.work_order")
                ->insert([
                    'work_order_number'     =>$request->work_order_number,
                    'workcenter_id'         =>$request->workcenter_id,
                    'product_id'            =>$request->product_id,
                    'work_order_qty'        =>$request->work_order_qty,
                    'bom_default'           =>$request->bom_default,
                    'start_date'            =>$request->start_date,
                    'est_finish_date'       =>$request->est_finish_date,
                    'parent_work_order_id'  =>$request->parent_work_order_id,
                    'work_order_type'       =>$request->work_order_type,
                    'work_order_status'     =>$request->work_order_status,
                    'created_by'            =>$request->created_by
                ]);
            
            if ($this->workOrder == 0) {
                return response()->json(['status' => 'error', 'message' => 'Failed insert ' . $request->work_order_number . ' , contact your administrator']);
            } else {
                return response()->json(['status' => 'ok', 'message' => $request->work_order_number . ' was inserted']);
            }
        }
    }

    public function update(Request $request){
        $validator = Validator::make($request->all(), [
            'work_order_hd_id'      =>'required',
            'work_order_number'     =>'required|unique:work_order,work_order_number',
            'workcenter_id'         =>'required|exists:workcenter,workcenter_id',
            'product_id'            =>'required|exists:product,product_id',
            'work_order_qty'        =>'required|numeric',
            'bom_default'           =>'required|exists:bom_hd,bom_hd_id',
            'start_date'            =>'required|date_format:Y-m-d|before_or_equal:est_finish_date',
            'est_finish_date'       =>'required|date_format:Y-m-d|after_or_equal:start_date',
            'parent_work_order_id'  =>'nullable|exists:work_order,work_order_hd_id',
            'work_order_type'       =>'required|exists:work_order_type,work_order_type_code',
            'work_order_status'     =>'required|exists:work_order_status,work_order_status_code',
            'updated_by'            =>'required',
            'reason'                =>'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            try{
                DB::beginTransaction();
                QueryController::reasonAction("update");
                $this->workOrder = DB::table("precise.work_order")
                ->where('work_order_hd_id', $request->work_order_hd_id)
                ->update([
                    'work_order_number'     =>$request->work_order_number,
                    'workcenter_id'         =>$request->workcenter_id,
                    'product_id'            =>$request->product_id,
                    'work_order_qty'        =>$request->work_order_qty,
                    'bom_default'           =>$request->bom_default,
                    'start_date'            =>$request->start_date,
                    'est_finish_date'       =>$request->est_finish_date,
                    'parent_work_order_id'  =>$request->parent_work_order_id,
                    'work_order_type'       =>$request->work_order_type,
                    'work_order_status'     =>$request->work_order_status,
                    'updated_by'            =>$request->created_by
                ]);
            
                if ($this->workOrder == 0) {
                    DB::rollback();
                    return response()->json(['status' => 'error', 'message' => 'Failed update ' . $request->work_order_number . ' , contact your administrator']);
                } else {
                    DB::commit();
                    return response()->json(['status' => 'ok', 'message' => $request->work_order_number . ' was updated']);
                }
            }catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
    }

    public function showImportCheckBOMAndProduct(Request $request){
        $validator = Validator::make($request->all(), [
            'bom_code'     => 'required',
            'product_code' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->workOrder = DB::table('precise.bom_hd')
                ->where('bom_code', $request->bom_code)
                ->where('product_id', $request->product_code)
                ->select(
                    'bom_hd_id',
                    'bom_code',
                    'bom_name'
                )
                ->get();
        }
        return response()->json(['data' => $this->workOrder]);
    }

    public function showImportCheckProductAndWorkcenter(Request $request){
        $validator = Validator::make($request->all(), [
            'product_code'      => 'required',
            'workcenter_code'   => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->workOrder = DB::table('precise.product_workcenter as pw')
                ->where('p.product_code', $request->product_code)
                ->where('w.workcenter_code', $request->workcenter_code)
                ->select(
                    'p.product_id',
                    'w.workcenter_id'
                )
                ->leftJoin("precise.product as p","pw.product_id","=","p.product_id")
                ->leftJoin("precise.workcenter as w","pw.workcenter_id","=","w.workcenter_id")
                ->get();
        }
        return response()->json(['data' => $this->workOrder]);
    }

    public function destroy($id){
        $validator = Validator::make($request->all(), [
            'deleted_by'            =>'required',
            'reason'                =>'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            DB::beginTransaction();
            try{
                QueryController::reasonAction("delete");
                $this->workOrder = DB::table('precise.work_order')
                    ->where('work_order_hd_id', $id)->delete();

                if ($this->workOrder == 0) {
                    DB::rollback();
                    return response()->json(['status' => 'error', 'message' => 'Failed delete work order , contact your administrator']);
                } else {
                    DB::commit();
                    return response()->json(['status' => 'ok', 'message' =>'work order was deleted']);
                }
            }catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
    }

    public function check(Request $request){
        $type = $request->get('type');
        $value = $request->get('value');
        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'value' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            if ($type == "number") {
                $this->workOrder = DB::table('precise.work_order')->where([
                    'work_order_number' => $value
                ])->count();
            }
            return response()->json(['status' => 'ok', 'message' => $this->workOrder]);
        }
    }
}
