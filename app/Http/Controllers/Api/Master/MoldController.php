<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\Helpers\QueryController;

class MoldController extends Controller
{
    private $mold;
    public function index($id){
        $this->mold = DB::table("precise.mold_hd as hd")
            ->where('hd.workcenter_id', $id)
            ->select(
                'hd.mold_hd_id',
                'hd.mold_number',
                'hd.mold_name', 
                'hd.workcenter_id',
                'workcenter_code',
                'workcenter_name',
                DB::raw(
                    "case hd.is_family_mold
                    when 1 then 'Ya'
                    when 0 then 'Tidak'
                    end as is_family_mold"
                ),
                'hd.customer_id',
                'customer_code',
                'customer_name',
                'hd.status_code',
                'status_description',
                'hd.remake_from',
                'hd.mold_description',
                'hd2.mold_number',
                'hd2.mold_name',
                'hd.length',
                'hd.width',
                'hd.height',
                'hd.dimension_uom', 
                'hd.weight',
                'hd.weight_uom', 
                'hd.plate_size_length',
                'hd.plate_size_width',
                'hd.plate_size_uom',
                'hd.created_on',
                'hd.created_by',
                'hd.updated_on',
                'hd.updated_by'	
            )
            ->leftJoin('precise.workcenter as w','hd.workcenter_id','=','w.workcenter_id')
            ->leftJoin('precise.customer as c','hd.customer_id','=','c.customer_id')
            ->leftJoin('precise.mold_status as ms','hd.status_code','=','ms.status_code')
            ->leftJoin('precise.mold_hd as hd2','hd.remake_from','=','hd2.mold_hd_id')
            ->get();

        return response()->json(["data" => $this->mold]);
    }

    public function show($id){
        $all = array();
        $cavity = array();

        $master = DB::table("precise.mold_hd as hd")
            ->where('hd.mold_hd_id', $id)
            ->select(
                'hd.mold_hd_id',
                'hd.mold_number',
                'hd.mold_name', 
                'hd.workcenter_id',
                'workcenter_code',
                'workcenter_name',
                DB::raw(
                    "case hd.is_family_mold
                    when 1 then 'Ya'
                    when 0 then 'Tidak'
                    end as is_family_mold"
                ),
                'hd.customer_id',
                'customer_code',
                'customer_name',
                'hd.status_code',
                'status_description',
                'hd.remake_from',
                'hd.mold_description',
                'hd2.mold_number',
                'hd2.mold_name',
                'hd.length',
                'hd.width',
                'hd.height',
                'hd.dimension_uom', 
                'hd.weight',
                'hd.weight_uom', 
                'hd.plate_size_length',
                'hd.plate_size_width',
                'hd.plate_size_uom'
            )
            ->leftJoin('precise.workcenter as w','hd.workcenter_id','=','w.workcenter_id')
            ->leftJoin('precise.customer as c','hd.customer_id','=','c.customer_id')
            ->leftJoin('precise.mold_status as ms','hd.status_code','=','ms.status_code')
            ->leftJoin('precise.mold_hd as hd2','hd.remake_from','=','hd2.mold_hd_id')
            ->first();
            

            $detail = DB::table("precise.mold_dt as dt")
                ->where('mold_hd_id', $id)
                ->select(
                    'mold_dt_id',
                    'mold_hd_id', 
                    'product_item_id',
                    'item_code',
                    'item_name'
                )
                ->leftJoin("precise.product_item as pi", "dt.product_item_id", "=", "pi.item_id")
                ->get();

            foreach($detail as $details){
                $cavity = DB::table('precise.mold_cavity as mc')
                    ->where('md.mold_hd_id', $id)
                    ->select(
                        'mold_cavity_id',
                        'mc.mold_dt_id',
                        'cavity_number',
                        'product_weight',
                        'product_weight_uom',
                        'is_active'
                    )
                    ->leftJoin('precise.mold_dt as md','mc.mold_dt_id','=','md.mold_dt_id')
                    ->get();
                
                $all[] = array(
                    "mold_dt_id"        => $details->mold_dt_id,
                    "mold_hd_id"        => $details->mold_hd_id,
                    "product_item_id"   => $details->product_item_id,
                    "item_code"         => $details->item_code,
                    "item_name"         => $details->item_name,
                    "detail_cavity"     => $cavity
                );
            }

            $this->mold = array(
                'mold_hd_id'         => $master->mold_hd_id,
                'mold_number'        => $master->mold_number,
                'mold_name'          => $master->mold_name,
                'workcenter_id'      => $master->workcenter_id,
                'workcenter_code'    => $master->workcenter_code,
                'workcenter_name'    => $master->workcenter_name,
                'is_family_mold'     => $master->is_family_mold,
                'customer_id'        => $master->customer_id,
                'customer_code'      => $master->customer_code,
                'customer_name'      => $master->customer_name,
                'status_code'        => $master->status_code,
                'status_description' => $master->status_description,
                'remake_from'        => $master->remake_from,
                'mold_description'   => $master->mold_description,
                'hd2.mold_number'    => $master->mold_number,
                'hd2.mold_name'      => $master->mold_name,
                'length'             => $master->length,
                'width'              => $master->width,
                'height'             => $master->height,
                'dimension_uom'      => $master->dimension_uom, 
                'weight'             => $master->weight,
                'weight_uom'         => $master->weight_uom, 
                'plate_size_length'  => $master->plate_size_length,
                'plate_size_width'   => $master->plate_size_width,
                'plate_size_uom'     => $master->plate_size_uom,
                'detail'             => $all
            );
        return response()->json($this->mold);
    }

    public function showByWorkcenter($id){
        $this->mold = DB::table("precise.mold_hd as hd")
            ->where('hd.workcenter_id', $id)
            ->select(
                "mold_hd_id",
                "mold_number",
                "mold_name",
                "mold_descrption"
            )
            ->get();
        
        return response()->json(["data" => $this->mold]);
    }

    public function showByProductItem($id){
        $this->mold = DB::table("precise.mold_hd as hd")
            ->where('dt.product_item_id', $id)
            ->select(
                "hd.mold_hd_id",
                "mold_number",
                "mold_name",
                "mold_descrption"
            )
            ->leftJoin("precise.mold_dt as dt", "hd.mold_hd_id", "=", "dt.mold_hd_id")
            ->get();
        
        return response()->json(["data" => $this->mold]);
    }

    public function create(Request $request){
        $data = $request->json()->all();
        $validator = Validator::make(json_decode(json_encode($data),true),[
            'mold_number'       => 'required|unique:mold_hd,mold_number',
            'mold_name'         => 'required|unique:mold_hd,mold_name',
            'workcenter_id'     => 'required|exists:workcenter,workcenter_id',
            'is_family_mold'    => 'required|boolean',
            'customer_id'       => 'required|exists:customer,customer_id',
            'status_code'       => 'required|exists:mold_status,status_code',
            'remake_from'       => 'nullable|exists:mold_hd,mold_hd_id',
            'length'            => 'required|numeric',
            'width'             => 'required|numeric',
            'height'            => 'required|numeric',
            'dimension_uom'     => 'required|exists:uom,uom_code',
            'weight'            => 'required|numeric',
            'weight_uom'        => 'required|exists:uom,uom_code',
            'plate_size_length' => 'required|numeric',
            'plate_size_width'  => 'required|numeric',
            'plate_size_uom'    => 'required|exists:uom,uom_code',
            'created_by'        => 'required',
            'detail'            => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            try{
                DB::beginTransaction();
                $id_hd = DB::table('precise.mold_hd')
                    ->insertGetId([
                        'mold_number'       =>$request->mold_number,
                        'mold_name'         =>$request->mold_name,
                        'workcenter_id'     =>$request->workcenter_id,
                        'is_family_mold'    =>$request->is_family_mold,
                        'customer_id'       =>$request->customer_id,
                        'status_code'       =>$request->status_code,
                        'remake_from'       =>$request->remake_from,
                        'mold_description'  =>$request->mold_description,
                        'length'            =>$request->length,
                        'width'             =>$request->width,
                        'height'            =>$request->height,
                        'dimension_uom'     =>$request->dimension_uom,
                        'weight'            =>$request->weight,
                        'weight_uom'        =>$request->weight_uom,
                        'plate_size_length' =>$request->plate_size_length,
                        'plate_size_width'  =>$request->plate_size_length,
                        'plate_size_uom'    =>$request->plate_size_uom,
                        'created_by'        =>$request->created_by
                ]);

                
                $validator = Validator::make(json_decode(json_encode($data['detail']),true),[
                    'product_item_id'   =>'required|exists:product_item,item_id',
                    'created_by'        =>'required',
                    'detail_cavity'     =>'required'
                ]);

                if ($validator->fails()) {
                    return response()->json(['status' => 'error', 'message' => $validator->errors()]);
                } else {
                    $detail = array();
                    foreach($data['detail'] as $d){
                        $id_dt = DB::table('precise.mold_dt')->insertGetId([
                            'mold_hd_id'        => $id_hd,
                            'product_item_id'   => $d['product_item_id'],
                            'created_by'        => $d['created_by']
                        ]);

                        $validator = Validator::make(json_decode(json_encode($data['detail']['detail_cavity']),true),[
                            'cavity_number'     =>'required',
                            'product_weight'    =>'required|numeric',
                            'product_weight_uom'=>'required|exists:uom,uom_code',
                            'is_active'         =>'required|boolean',
                            'created_by'        =>'required'
                        ]);

                        if ($validator->fails()) {
                            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
                        } else {
                            $detail[] = null;
                            foreach($data['detail']['detail_cavity'] as $dc){
                                $detail = [
                                    'mold_dt_id'        =>$id_dt,
                                    'cavity_number'     =>$dc['cavity_number'],
                                    'product_weight'    =>$dc['product_weight'],
                                    'product_weight_uom'=>$dc['product_weight_uom'],
                                    'is_active'         =>$dc['is_active'],
                                    'created_by'        =>$dc['created_by']
                                ];
                            }

                            DB::table('precise.cavity_number')
                            ->insert($detail);
                        }
                    }
                }

                $trans = DB::table('precise.mold_hd')
                            ->where('mold_hd_id', $id_hd)
                            ->select('mold_number')
                            ->first();

                DB::commit();
                return response()->json(['status' => 'ok', 'message' => $trans->mold_number]);
            }catch(\Exception $e){
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
    }

    public function update(Request $request){
        $data = $request->json()->all();
        $validator = Validator::make(json_decode(json_encode($data),true),[
            'mold_hd_id'        => 'required|exists:mold_hd,mold_hd_id',
            'mold_number'       => 'required|unique:mold_hd,mold_number',
            'mold_name'         => 'required|unique:mold_hd,mold_name',
            'workcenter_id'     => 'required|exists:workcenter,workcenter_id',
            'is_family_mold'    => 'required|boolean',
            'customer_id'       => 'required|exists:customer,customer_id',
            'status_code'       => 'required|exists:mold_status,status_code',
            'remake_from'       => 'nullable|exists:mold_hd,mold_hd_id',
            'length'            => 'required|numeric',
            'width'             => 'required|numeric',
            'height'            => 'required|numeric',
            'dimension_uom'     => 'required|exists:uom,uom_code',
            'weight'            => 'required|numeric',
            'weight_uom'        => 'required|exists:uom,uom_code',
            'plate_size_length' => 'required|numeric',
            'plate_size_width'  => 'required|numeric',
            'plate_size_uom'    => 'required|exists:uom,uom_code',
            'updated_by'        => 'required',
            'detail'            => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            DB::beginTransaction();
            try{
                QueryController::reason($data);
                $this->mold = DB::table('precise.mold_hd')
                    ->where('mold_hd_id', $data['mold_hd_id'])
                    ->update([
                        'mold_number'       =>$data['mold_number'],
                        'mold_name'         =>$data['mold_name'],
                        'workcenter_id'     =>$data['workcenter_id'],
                        'is_family_mold'    =>$data['is_family_mold'],
                        'customer_id'       =>$data['customer_id'],
                        'status_code'       =>$data['status_code'],
                        'remake_from'       =>$data['remake_from'],
                        'mold_description'  =>$data['mold_description'],
                        'length'            =>$data['length'],
                        'width'             =>$data['width'],
                        'height'            =>$data['height'],
                        'dimension_uom'     =>$data['dimension_uom'],
                        'weight'            =>$data['weight'],
                        'weight_uom'        =>$data['weight_uom'],
                        'plate_size_length' =>$data['plate_size_length'],
                        'plate_size_width'  =>$data['plate_size_length'],
                        'plate_size_uom'    =>$data['plate_size_uom'],
                        'updated_by'        =>$data['updated_by']
                    ]);
                
                if($data['inserted_detail'] != null)
                {
                    //$dt = array();
                    foreach($data['inserted_detail'] as $d)
                    {
                        $dt = [
                            'mold_hd_id'        => $data['mold_hd_id'],
                            'product_item_id'   => $d['product_item_id'],
                            'created_by'        => $d['created_by']
                        ];
                        $detail_id = DB::table('precise.mold_dt')
                            ->insertGetId($dt);

                        foreach($data['inserted_detail']['cavity'] as $dc)
                        {
                            $dtc[] = [
                                'mold_dt_id'        =>$detail_id,
                                'cavity_number'     =>$dc['cavity_number'],
                                'product_weight'    =>$dc['product_weight'],
                                'product_weight_uom'=>$dc['product_weight_uom'],
                                'is_active'         =>$dc['is_active'],
                                'created_by'        =>$dc['created_by']
                            ];   
                        }
                        DB::table('precise.mold_cavity')
                                ->insert($dtc);
                    }
                    
                }

                if($data['updated_detail'] != null)
                {
                    foreach($data['updated_detail'] as $d)
                    {
                        DB::table('precise.mold_dt')
                        ->where('mold_dt_id', $d['mold_dt_id'])
                        ->update([
                            'mold_hd_id'        => $data['mold_hd_id'],
                            'product_item_id'   => $d['product_item_id'],
                            'updated_by'        => $d['updated_by']
                        ]);
                    }
                }

                if($data['deleted_detail'] != null)
                {
                    $delete = array();
                    foreach($data['deleted_detail'] as $d)
                    {
                        $delete[] = $d['mold_dt_id'];
                    }
                    DB::table('precise.mold_dt')
                        ->whereIn('mold_dt_id', $delete)
                        ->delete();
                }

                if($data['detail_cavity']!= null){
                    if($data['detail_cavity']['updated'] != null)
                    {
                        foreach($data['detail_cavity']['updated'] as $uc)
                        {
                            DB::table('precise.mold_cavity')
                                ->where('mold_cavity_id', $uc['mold_cavity_id'])
                                ->update([
                                    'mold_dt_id'        =>$uc['mold_dt_id'],
                                    'cavity_number'     =>$uc['cavity_number'],
                                    'product_weight'    =>$uc['product_weight'],
                                    'product_weight_uom'=>$uc['product_weight_uom'],
                                    'is_active'         =>$uc['is_active'],
                                    'updated_by'        =>$uc['updated_by']
                                ]);
                        }
                    }

                    if($data['detail_cavity']['deleted'] != null)
                    {
                        $delete = array();
                        foreach($data['detail_cavity']['deleted'] as $d)
                        {
                            $delete[] = $d['mold_cavity_id'];
                        }
                        DB::table('precise.mold_cavity')
                            ->whereIn('mold_cavity_id', $delete)
                            ->delete();
                    }
                }

                if ($this->mold == 0) {
                    DB::rollback();
                    return response()->json(['status' => 'error', 'message' => 'Failed to update Mold']);
                } else {
                    DB::commit();
                    return response()->json(['status' => 'ok', 'message' => 'Mold has been updated']);
                }
            }catch(\Exception $e){
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
                $this->mold = DB::table('mold_hd')->where('mold_number', $value)->count();
            }else if($type == "name"){
                $this->mold = DB::table('mold_hd')->where('mold_name', $value)->count();
            }

            return response()->json(['status' => 'ok', 'message' => $this->mold]);
        }
    }
}
