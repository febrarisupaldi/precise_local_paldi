<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\Master\HelperController;

class MachineController extends Controller
{
    private $machine;
    public function index(){
        try{
            $this->machine = DB::table('precise.machine as m')
                ->select(
                    'machine_id',
                    'machine_code',
                    'machine_name',
                    'machine_brand',
                    'machine_model',
                    'serial_number',
                    'tonnage',
                    'manufacture_date',
                    'acquisition_date',
                    'active_date',
                    'inactive_date',  
                    'm.workcenter_id',
                    'w.workcenter_code',
                    'w.workcenter_name',
                    'lane_code',
                    'lane_number',
                    'm.is_active',
                    'm.created_on',
                    'm.created_by',
                    'm.updated_on',
                    'm.updated_by'
                )
                ->leftJoin('precise.workcenter as w','m.workcenter_id','=','w.workcenter_id')
                ->get();
            return response()->json(["data"=> $this->machine]);
        }catch(\Exception $e){
            return response()->json(["data"=> $e->getMessage()]);
        }
    }

    public function show($id)
    {
        try{
            $this->machine = DB::table('precise.machine as m')
                ->where('machine_id', $id)
                ->select(
                    'machine_id',
                    'machine_code',
                    'machine_name',
                    'machine_brand',
                    'machine_model',
                    'serial_number',
                    'tonnage',
                    'manufacture_date',
                    'acquisition_date',
                    'active_date',
                    'inactive_date',
                    'm.workcenter_id',
                    'lane_code',
                    'lane_number',
                    'm.is_active',
                    'm.created_on',
                    'm.created_by',
                    'm.updated_on',
                    'm.updated_by'
                )
                ->first();

            return response()->json($this->machine);
        }catch(\Exception $e){
            return response()->json($e->getMessage());
        }
    }

    public function show_by_workcenter(Request $request){
        $wc = $request->get('id');
        $validator = Validator::make(
            $request->all(),
            [
                'id'    => 'required'
            ]
        );
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            try{
                $workcenter = explode("-", $wc);
                $this->machine = DB::table('precise.machine as m')
                    ->whereIn('m.workcenter_id', $workcenter)
                    ->select(
                        'machine_id',
                        'machine_code',
                        'machine_name',
                        'machine_brand',
                        'machine_model',
                        'serial_number',
                        'tonnage',
                        'manufacture_date',
                        'acquisition_date',
                        'active_date',
                        'inactive_date',  
                        'm.workcenter_id',
                        'w.workcenter_code',
                        'w.workcenter_name',
                        'lane_code',
                        'lane_number',
                        'm.is_active',
                        'm.created_on',
                        'm.created_by',
                        'm.updated_on',
                        'm.updated_by'
                    )
                    ->leftJoin('precise.workcenter as w','m.workcenter_id','=','w.workcenter_id')
                    ->get();

                return response()->json(["data"=>$this->machine]);
            }catch(\Exception $e){
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
    }

    public function create(Request $request){
        $validator = Validator::make(
            $request->all(),
            [
                'machine_code'      => 'required',
                'machine_name'      => 'required',
                'machine_brand'     => 'required',
                'machine_model'     => 'required',
                'serial_number'     => 'required',
                'tonnage'           => 'required|numeric',
                'manufacture_date'  => 'required|date_format:Y-m-d',
                'workcenter_id'     => 'required|exists:workcenter,workcenter_id',
                'lane_code'         => 'required',
                'lane_number'       => 'required',
                'created_by'        => 'required'
            ]
        );

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            try{
                $this->machine = DB::table('precise.machine')
                ->insert([
                    'machine_code'      => $request->machine_code,
                    'machine_name'      => $request->machine_name,
                    'machine_brand'     => $request->machine_brand,
                    'machine_model'     => $request->machine_model,
                    'serial_number'     => $request->serial_number,
                    'tonnage'           => $request->tonnage,
                    'manufacture_date'  => $request->manufacture_date,
                    'acquisition_date'  => $request->acquisition_date,
                    'active_date'       => $request->active_date,
                    'inactive_date'     => $request->inactive_date,
                    'workcenter_id'     => $request->workcenter_id,
                    'lane_code'         => $request->lane_code,
                    'lane_number'       => $request->lane_number,
                    'created_by'        => $request->created_by
                ]);

                if($this->machine == 0){
                    return response()->json(['status' => 'error', 'message' => 'Failed to input machine, contact your administrator']);
                }else{
                    return response()->json(['status' => 'ok', 'message' => $request->machine_name . ' has been inputed']);
                }
            }catch(\Exception $e){
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
    }

    public function update(Request $request){
        $validator = Validator::make(
            $request->all(),
            [
                'machine_id'        => 'required',
                'machine_name'      => 'required',
                'machine_brand'     => 'required',
                'machine_model'     => 'required',
                'serial_number'     => 'required',
                'tonnage'           => 'required|numeric',
                'manufacture_date'  => 'required|date_format:Y-m-d',
                'workcenter_id'     => 'required|exists:workcenter,workcenter_id',
                'lane_code'         => 'required',
                'lane_number'       => 'required',
                'updated_by'        => 'required'
            ]
        );

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            DB::beginTransaction();
            try{
                $helper = new HelperController();
                $helper->reason("update");

                $this->machine = DB::table('precise.machine')
                    ->where('machine_id', $request->machine_id)
                    ->update([
                        'machine_code'      => $request->machine_code,
                        'machine_name'      => $request->machine_name,
                        'machine_brand'     => $request->machine_brand,
                        'machine_model'     => $request->machine_model,
                        'serial_number'     => $request->serial_number,
                        'tonnage'           => $request->tonnage,
                        'manufacture_date'  => $request->manufacture_date,
                        'acquisition_date'  => $request->acquisition_date,
                        'active_date'       => $request->active_date,
                        'inactive_date'     => $request->inactive_date,
                        'workcenter_id'     => $request->workcenter_id,
                        'lane_code'         => $request->lane_code,
                        'lane_number'       => $request->lane_number,
                        'is_active'         => $request->is_active,
                        'updated_by'        => $request->updated_by
                    ]);

                if ($this->machine == 0) {
                    DB::rollback();
                    return response()->json(['status' => 'error', 'message' => 'Failed to update machine, Contact your administrator']);
                } else {
                    DB::commit();
                    return response()->json(['status' => 'ok', 'message' => 'Machine has been updated']);
                }
            }catch(\Exception $e){
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
    }

    public function check(Request $request){
        $type = $request->get('type');
        $validator = Validator::make($request->all(), [
            'type' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            try{
                if($type == "code"){
                    $value = $request->get('value');
                    $validator = Validator::make($request->all(), [
                        'value' => 'required'
                    ]);
                    if ($validator->fails()) {
                        return response()->json(['status' => 'error', 'message' => $validator->errors()]);
                    } else {
                        $this->machine = DB::table('precise.machine')
                            ->where('machine_code', $value)
                            ->select(
                                'machine_code'
                            )
                            ->count();
                    }
                }else if($type=="lane"){
                    $workcenter = $request->get('workcenter');
                    $code = $request->get('lane_code');
                    $number = $request->get('lane_number');

                    $validator = Validator::make($request->all(), [
                        'workcenter' => 'required',
                        'lane_code'  => 'required',
                        'lane_number'=> 'required'
                    ]);

                    if ($validator->fails()) {
                        return response()->json(['status' => 'error', 'message' => $validator->errors()]);
                    } else {
                        $this->machine = DB::table('precise.machine')
                            ->where('workcenter_id', $workcenter)
                            ->where('lane_code', $code)
                            ->where('lane_number', $number)
                            ->select(
                                'machine_code'
                            )
                            ->count();
                    }
                }
                return response()->json(['status' => 'ok', 'message' => $this->machine]);
            }
            catch(\Exception $e){
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
    }
}
