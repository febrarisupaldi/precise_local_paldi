<?php

namespace App\Http\Controllers\Api\OEM;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\Master\HelperController;
use App\Http\Controllers\Api\Helpers\QueryController;

class MaterialIncomingController extends Controller
{
    private $materialIncoming, $checkMaterialIncoming;
    public function index(Request $request)
    {
        $start = $request->get('start');
        $end = $request->get('end');
        $wh = $request->get('warehouse');
        $validator = Validator::make($request->all(), [
            'start'     => 'required|date_format:Y-m-d|before_or_equal:end',
            'end'       => 'required|date_format:Y-m-d|after_or_equal:start',
            'warehouse' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $warehouse = explode('-', $wh);
            $this->materialIncoming = DB::table('precise.oem_material_trans_hd as hd')
            ->whereBetween('hd.trans_date',[$start, $end])
            ->whereIn('hd.warehouse_id',$warehouse)
            ->select(
                'material_trans_hd_id',
                'trans_number as Nomor transaksi',
                'trans_date as Tanggal kedatangan',
                DB::raw("
                    concat(c.customer_code, ' - ', c.customer_name) as 'Customer',
                    concat(w.warehouse_code, ' - ', w.warehouse_name) as 'Gudang'
                "),
                'trans_description as Keterangan',
                'hd.created_on as Tanggal input',
                'hd.created_by as User input',
                'hd.updated_on as Tanggal update',
                'hd.updated_by as User update'
            )
            ->leftJoin('precise.customer as c','hd.customer_id','=','c.customer_id')
            ->leftJoin('precise.warehouse as w','hd.warehouse_id','=','w.warehouse_id')
            ->get();

            return response()->json(['data'=> $this->materialIncoming]);
        }
    }

    public function show($id)
    {
        $master = DB::table('precise.oem_material_trans_hd as hd')
        ->where('material_trans_hd_id', $id)
        ->select(
            'material_trans_hd_id',
            'trans_number',
            'trans_date',
            'trans_type_id',
            'hd.customer_id',
            'c.customer_code',
            'c.customer_name',
            'hd.warehouse_id',
            'w.warehouse_code',
            'w.warehouse_name',
            'trans_description',
            'hd.created_on',
            'hd.created_by',
            'hd.updated_on',
            'hd.updated_by'
        )
        ->leftJoin('precise.customer as c','hd.customer_id','=','c.customer_id')
        ->leftJoin('precise.warehouse as w','hd.warehouse_id','=','w.warehouse_id')
        ->firstOrFail();
        
        

        $detail = DB::table('precise.oem_material_trans_dt as dt')
        ->where('material_trans_hd_id', $master->material_trans_hd_id)
        ->select('material_trans_dt_id',
        'material_trans_hd_id',
        'dt.material_customer_hd_id',
        'mch.material_id',
        'product_code as material_code',
        'product_name as material_name',
        'mch.customer_id',
        'c.customer_code',
        'c.customer_name',
        'material_qty',
        'material_uom',
        'dt.created_on',
        'dt.created_by',
        'dt.updated_on',
        'dt.updated_by')
        ->leftJoin('precise.material_customer_hd as mch','dt.material_customer_hd_id','=','mch.material_customer_hd_id')
        ->leftJoin('precise.product as p','mch.material_id','=','p.product_id')
        ->leftJoin('precise.customer as c','mch.customer_id','=','c.customer_id')
        ->get();

        $this->materialIncoming = array(
                "material_trans_hd_id" => $master->material_trans_hd_id, 
                "trans_number"         => $master->trans_number,
                "trans_date"           => $master->trans_date,
                "trans_type_id"        => $master->trans_type_id,
                "customer_id"          => $master->customer_id,
                "customer_code"        => $master->customer_code,
                "customer_name"        => $master->customer_name,
                "warehouse_id"         => $master->warehouse_id,
                "warehouse_code"       => $master->warehouse_code,
                "warehouse_name"       => $master->warehouse_name,
                "trans_description"    => $master->trans_description,
                "created_on"           => $master->created_on,
                "created_by"           => $master->created_by,
                "updated_on"           => $master->updated_on,
                "updated_by"           => $master->updated_by,
                "detail"               => $detail
        );
        
        return response()->json($this->materialIncoming);
    }

    public function joined(Request $request)
    {
        $start = $request->get('start');
        $end = $request->get('end');
        $wh = $request->get('warehouse');
        $validator = Validator::make($request->all(), [
            'start'     => 'required|date_format:Y-m-d|before_or_equal:end',
            'end'       => 'required|date_format:Y-m-d|after_or_equal:start',
            'warehouse' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $warehouse = explode('-', $wh);
            $this->materialIncoming = DB::table('precise.oem_material_trans_hd as hd')
            ->whereBetween('hd.trans_date',[$start, $end])
            ->whereIn('hd.warehouse_id',$warehouse)
            ->select(
                'hd.material_trans_hd_id',
                'trans_number as Nomor transaksi',
                'trans_date as Tanggal kedatangan',
                DB::raw("
                    concat(c.customer_code, ' - ', c.customer_name) as 'Customer',
                    concat(w.warehouse_code, ' - ', w.warehouse_name) as 'Gudang'
                "),
                'trans_description as Keterangan',
                'p.product_code as Kode material',
                'p.product_name as Nama material',
                'dt.material_qty as Qty kedatangan',
                'dt.material_uom as UOM',
                'hd.created_on as Tanggal input',
                'hd.created_by as User input',
                'hd.updated_on as Tanggal update',
                'hd.updated_by as User update'
            )
            ->leftJoin('precise.oem_material_trans_dt as dt','hd.material_trans_hd_id','=','dt.material_trans_hd_id')
            ->leftJoin('precise.customer as c','hd.customer_id','=','c.customer_id')
            ->leftJoin('precise.warehouse as w','hd.warehouse_id','=','w.warehouse_id')
            ->leftJoin('precise.material_customer_hd as mch','dt.material_customer_hd_id','=','mch.material_customer_hd_id')
            ->leftJoin('precise.product as p','mch.material_id','=','p.product_id')
            ->get();

            return response()->json(['data'=> $this->materialIncoming]);
        }
    }

    public function create(Request $request)
    {
        $data = $request->json()->all();

        $validator = Validator::make(json_decode(json_encode($data),true),[
            'trans_date'    => 'required',
            'trans_type_id' => 'required',
            'customer_id'   => 'required|exists:customer,customer_id',
            'warehouse_id'  => 'required|exists:warehouse,warehouse_id',
            'created_by'    => 'required',
            'detail'        => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            DB::beginTransaction();
            try{
                $id = DB::table('precise.oem_material_trans_hd')
                ->insertGetId([
                    'trans_date'        => $data['trans_date'],
                    'trans_type_id'     => $data['trans_type_id'],
                    'customer_id'       => $data['customer_id'],
                    'warehouse_id'      => $data['warehouse_id'],
                    'trans_description' => $data['trans_description'],
                    'created_by'        => $data['created_by']
                ]);
                foreach($data['detail'] as $d)
                {
                    $dt[] = [
                        'material_trans_hd_id'    => $id,
                        'material_customer_hd_id' => $d['material_customer_hd_id'],
                        'material_qty'            => $d['material_qty'],
                        'material_uom'            => $d['material_uom'],
                        'created_by'              => $d['created_by']
                    ];
                }
                DB::table('precise.oem_material_trans_dt')
                ->insert($dt);
		
		        $trans = DB::table('precise.oem_material_trans_hd')
                        ->where('material_trans_hd_id', $id)
                        ->select('trans_number')
                        ->first();

                DB::commit();
                return response()->json(['status' => 'ok', 'message' => $trans->trans_number]);
            }catch(\Exception $e){
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
    }

    public function update(Request $request)
    {
        $data = $request->json()->all();

        $validator = Validator::make(json_decode(json_encode($data),true),[
            'trans_date'    => 'required',
            'customer_id'   => 'required|exists:customer,customer_id',
            'warehouse_id'  => 'required|exists:warehouse,warehouse_id',
            'updated_by'    => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            DB::beginTransaction();
            try{
                QueryController::reason($data);
                DB::table('precise.oem_material_trans_hd')
                ->where('material_trans_hd_id', $data['material_trans_hd_id'])
                ->update([
                    'trans_date'        => $data['trans_date'],
                    'customer_id'       => $data['customer_id'],
                    'warehouse_id'      => $data['warehouse_id'],
                    'trans_description' => $data['trans_description'],
                    'updated_by'        => $data['updated_by']
                ]);

                if($data['inserted'] != null)
                {
                    foreach($data['inserted'] as $d)
                    {
                        $dt[] = [
                            'material_trans_hd_id'    => $d['material_trans_hd_id'],
                            'material_customer_hd_id' => $d['material_customer_hd_id'],
                            'material_qty'            => $d['material_qty'],
                            'material_uom'            => $d['material_uom'],
                            'created_by'              => $d['created_by']
                        ];
                    }
                    DB::table('precise.oem_material_trans_dt')
                    ->insert($dt);
                }

                if($data['updated'] != null)
                {
                    foreach($data['updated'] as $d)
                    {
                        DB::table('precise.oem_material_trans_dt')
                        ->where('material_trans_dt_id', $d['material_trans_dt_id'])
                        ->update([
                            'material_customer_hd_id' => $d['material_customer_hd_id'],
                            'material_qty'            => $d['material_qty'],
                            'material_uom'            => $d['material_uom'],
                            'updated_by'              => $d['updated_by']
                        ]);
                    }
                }

                if($data['deleted'] != null)
                {
                    $delete = array();
                    foreach($data['deleted'] as $del){
                        $delete[] = $del['material_trans_dt_id'];
                    }

                    DB::table('precise.oem_material_trans_dt')
                    ->whereIn('material_trans_dt_id', $delete)
                    ->delete();
                }
                DB::commit();
                return response()->json(['status' => 'ok', 'message' => 'Material Incoming have been updated']);
            }catch(\Exception $e){
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $helper = new HelperController();
            $helper->reason("delete");

             DB::table('precise.oem_material_trans_dt as dt')
            ->join('precise.oem_material_trans_hd as hd','dt.material_trans_hd_id','=','hd.material_trans_hd_id')
            ->whereRaw("dt.material_trans_hd_id = if('".$id."' regexp '^-?[0-9]+$' = 1,'".$id."', 0)")
            ->orWhereRaw("trans_number = cast('".$id."' as char)")
            ->delete();

            DB::table('precise.oem_material_trans_hd')
            ->whereRaw("material_trans_hd_id = if('".$id."' regexp '^-?[0-9]+$' = 1,'".$id."', 0)")
            ->orWhereRaw("trans_number = cast('".$id."' as char)")
            ->delete();
            DB::commit();
            return response()->json(['status' => 'ok', 'message' => 'Material Incoming have been deleted']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
