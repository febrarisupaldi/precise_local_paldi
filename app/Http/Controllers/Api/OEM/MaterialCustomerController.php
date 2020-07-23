<?php

namespace App\Http\Controllers\Api\OEM;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\Master\HelperController;
use App\Http\Controllers\Api\Helpers\QueryController;

class MaterialCustomerController extends Controller
{
    private $materialCustomer, $checkMaterialCustomer;
    public function index()
    {
        $this->materialCustomer = DB::table('precise.material_customer_hd as hd')
            ->select(
                'material_customer_hd_id',
                'product_code as Kode material',
                'product_name as Nama material',
                'customer_code as Kode customer',
                'customer_name as Nama customer',
                DB::raw("
                    case hd.is_active 
                        when 0 then 'Tidak aktif'
                        when 1 then 'Aktif' 
                    end as 'Status aktif'
                "),
                'hd.created_on as Tanggal input',
                'hd.created_by as User input',
                'hd.updated_on as Tanggal update',
                'hd.updated_by as User update'
            )->leftJoin('precise.product as prod', 'hd.material_id', '=', 'prod.product_id')
            ->leftJoin('precise.customer as cust', 'hd.customer_id', '=', 'cust.customer_id')
            ->get();

        return response()->json(['data' => $this->materialCustomer]);
    }

    public function show_material($id)
    {
        $this->materialCustomer = DB::table('precise.material_customer_hd as hd')
        ->where('material_id', $id)
        ->select(
            'material_id',
            'hd.customer_id',
            'cust.customer_code',
            'cust.customer_name'
        )->leftJoin('precise.customer as cust','hd.customer_id','=','cust.customer_id')
        ->get();

        return response()->json(['data' => $this->materialCustomer]);
    }

    public function joined()
    {
        $this->materialCustomer = DB::table('precise.material_customer_hd as hd')
        ->select(
            'hd.material_customer_hd_id',
            'prod1.product_code as Kode material',
            'prod1.product_name as Nama material',
            'customer_code as Kode customer',
            'customer_name as Nama customer',
            'prod2.product_code as Kode barang',
            'prod2.product_name as Nama barang',
            DB::raw(
                "case hd.is_active
                    when 0 then 'Tidak aktif'
                    when 1 then 'Aktif' 
                end as 'Status aktif'"
            ), 
            'dt.created_on as Tanggal input',
            'dt.created_by as User input',
            'dt.updated_on as Tanggal update',
            'dt.updated_by as User update'
        )
        ->leftJoin('precise.material_customer_dt as dt','hd.material_customer_hd_id','=','dt.material_customer_hd_id')
        ->leftJoin('precise.product_customer as pc','dt.product_customer_id','=','pc.product_customer_id')
        ->leftJoin('precise.product as prod1','hd.material_id','=','prod1.product_id')
        ->leftJoin('precise.product as prod2','pc.product_id','=','prod2.product_id')
        ->leftJoin('precise.customer as cust','hd.customer_id','=','cust.customer_id')
        ->get();

        return response()->json(['data' => $this->materialCustomer]);
    }

    public function show_product_customer($id)
    {
        $this->materialCustomer = DB::table('precise.material_customer_dt as dt')
        ->where('dt.product_customer_id', $id)
        ->select(
            'dt.product_customer_id',
            'prod.product_id',
            'prod.product_code',
            'prod.product_name'
            )
        ->leftJoin('precise.material_customer_hd as hd','hd.material_customer_hd_id','=','dt.material_customer_hd_id')
        ->leftJoin('precise.product as prod','hd.material_id','=','prod.product_id')
        ->get();

        return response()->json(['data' => $this->materialCustomer]);
    }

    public function show_customer($id)
    {
        $this->materialCustomer = DB::table('precise.material_customer_hd as hd')
        ->where('hd.customer_id', $id)
        ->select(
            'material_customer_hd_id',
            'material_id',
            'product_code as Kode material',
            'product_name as Nama material',
	    'prod.uom_code as UOM',
            DB::raw("
                case hd.is_active 
                    when 0 then 'Tidak aktif'
                    when 1 then 'Aktif' 
                end as 'Status aktif'
            "))
        ->leftJoin('precise.product as prod','hd.material_id','=','prod.product_id')
        ->leftJoin('precise.customer as cust','hd.customer_id','=','cust.customer_id')
        ->get();

        return response()->json(['data' => $this->materialCustomer]);
    }

    public function show($id)
    {
        $master = DB::table('precise.material_customer_hd as hd')
        ->where('material_customer_hd_id', $id)
        ->select(
            'material_customer_hd_id', 
            'material_id',
            'product_code',
            'product_name', 
            'hd.customer_id',
            'customer_code',
            'customer_name',
            'hd.is_active',
            'hd.created_on',
            'hd.created_by',
            'hd.updated_on',
            'hd.updated_by')
        ->leftJoin('precise.product as prod','hd.material_id','=','prod.product_id')
        ->leftJoin('precise.customer as cust','hd.customer_id','=','cust.customer_id')
        ->first();

        $detail = DB::table('precise.material_customer_dt as dt')
        ->where('material_customer_hd_id', $master->material_customer_hd_id)
        ->select(
            'material_customer_dt_id',
            'material_customer_hd_id',
            'dt.product_customer_id',
            'product_code',
            'product_name', 
            'dt.is_active', 
            'dt.created_on',
            'dt.created_by', 
            'dt.updated_on',
            'dt.updated_by'
        )
        ->leftJoin('precise.product_customer as pc','dt.product_customer_id','=','pc.product_customer_id')
        ->leftJoin('precise.product as prod','pc.product_id','=','prod.product_id')
        ->get();

        $this->materialCustomer = 
        array(
            "material_customer_hd_id" => $master->material_customer_hd_id,
            "material_id"             => $master->material_id,
            "product_code"            => $master->product_code,
            "product_name"            => $master->product_name, 
            "customer_id"             => $master->customer_id,
            "customer_code"           => $master->customer_code,
            "customer_name"           => $master->customer_name,
            "is_active"               => $master->is_active,
            "created_on"              => $master->created_on,
            "created_by"              => $master->created_by,
            "updated_on"              => $master->updated_on,
            "updated_by"              => $master->updated_by,
            "detail"                  => $detail
        );
        
        return response()->json($this->materialCustomer);
    }

    public function create(Request $request)
    {
        $data = $request->json()->all();

        $validator = Validator::make(json_decode(json_encode($data),true),[
            'material_id' => 'required|exists:product,product_id',
            'customer_id' => 'required|exists:customer,customer_id',
            'created_by'  => 'required',
            'detail'      => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $dt = array();
            DB::beginTransaction();
            try{
                $id = DB::table('precise.material_customer_hd')
                ->insertGetId([
                    'material_id' => $data['material_id'],
                    'customer_id' => $data['customer_id'],
                    'created_by'  => $data['created_by']
                ]);
                foreach($data['detail'] as $d)
                {
                    $dt[] = [
                        'material_customer_hd_id' => $id,
                        'product_customer_id'     => $d['product_customer_id'],
                        'created_by'              => $d['created_by']
                    ];
                }
                DB::table('precise.material_customer_dt')
                ->insert($dt);
                DB::commit();
                return response()->json(['status' => 'ok', 'message' => 'Material have been added']);
            }catch(\Exception $e){
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
            // return $data;
        }
    }

    public function update(Request $request)
    {
        $data = $request->json()->all();
        $validator = Validator::make(json_decode(json_encode($data),true),[
            'material_id' => 'required',
            'customer_id' => 'required',
            'updated_by'  => 'required',
            'reason'      => 'required',
            'is_active'   => 'required|boolean'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            DB::beginTransaction();
            try{
                QueryController::reason($data);
                DB::table('precise.material_customer_hd')
                ->where('material_customer_hd_id', $data['material_customer_hd_id'])
                ->update([
                    'material_id' => $data['material_id'],
                    'customer_id' => $data['customer_id'],
                    'is_active'   => $data['is_active'],
                    'updated_by'  => $data['updated_by']
                ]);
                
                if($data['inserted'] != null)
                {
                    foreach($data['inserted'] as $d)
                    {
                        $dt[] = [
                            'material_customer_hd_id' => $d['material_customer_hd_id'],
                            'product_customer_id'     => $d['product_customer_id'],
                            'created_by'              => $d['created_by']
                        ];
                    }
                    DB::table('precise.material_customer_dt')
                    ->insert($dt);
                }

                if($data['updated'] != null)
                {
                    foreach($data['updated'] as $d)
                    {
                        DB::table('precise.material_customer_dt')
                        ->where('material_customer_dt_id', $d['material_customer_dt_id'])
                        ->update([
                            'material_customer_hd_id' => $d['material_customer_hd_id'],
                            'product_customer_id'     => $d['product_customer_id'],
                            'updated_by'              => $d['updated_by']
                        ]);
                    }
                }

                // if($data['detail'] != null)
                // {
                //     DB::insert(
                //         QueryController::insertOrUpdate($data['detail'], 'material_customer_dt')
                //     );
                // }

                if($data['deleted'] != null)
                {
                    $delete = array();
                    foreach($data['deleted'] as $del){
                        $delete[] = $del['material_customer_dt_id'];
                    }
                    DB::table('precise.material_customer_dt')
                    ->whereIn('material_customer_dt_id', $delete)
                    ->delete();
                }
                DB::commit();
                return response()->json(['status' => 'ok', 'message' => 'Material Customer have been updated']);
            }
            catch(\Exception $e){
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
    }
}
