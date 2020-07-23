<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\Helpers\QueryController;

class ProductWorkcenterController extends Controller
{
    private $productWorkcenter;
    public function index($id){
        $id = explode('-', $id);
        $this->productWorkcenter = DB::table('precise.product_workcenter as pw')
            ->whereIn('w.workcenter_id', $id)
            ->select(
                'pw.product_workcenter_id',
                'p.product_code',
                'p.product_name',
                'w.workcenter_code',
                'w.workcenter_name',
                'bom.bom_code',
                'bom.bom_name',
                'pw.created_on',
                'pw.created_by',
                'pw.updated_on',
                'pw.updated_by'
            )
            ->leftJoin('precise.product as p','pw.product_id','=','p.product_id')
            ->leftJoin('precise.workcenter as w','pw.workcenter_id','=','w.workcenter_id')
            ->leftJoin('precise.bom_hd as bom','pw.bom_hd_id','=','bom.bom_hd_id')
            ->get();
        return response()->json(["data" => $this->productWorkcenter]);
    }

    public function show($id)
    {
        $id = explode('-', $id);
        $this->productWorkcenter = DB::table('product_workcenter as pw')
            ->whereIn('pw.product_workcenter_id', $id)
            ->select(
                'pw.product_workcenter_id',
                'pw.product_id',
                'pw.workcenter_id',
                'pw.bom_default',
                'p.product_code',
                'p.product_name',
                'w.workcenter_code',
                'w.workcenter_name',
                'bom.bom_code',
                'bom.bom_name',
                'pw.created_on as Tanggal input',
                'pw.created_by as User input',
                'pw.updated_on as Tanggal update',
                'pw.updated_by as User update'
            )
            ->leftJoin('precise.product as p', 'a.product_id', '=', 'p.product_id')
            ->leftJoin('precise.workcenter as w', 'a.workcenter_id', '=', 'w.workcenter_id')
            ->leftJoin('precise.bom_hd as bom','pw.bom_hd_id','=','bom.bom_hd_id')
            ->get();

        return response()->json(["data" => $this->productWorkcenter]);
    }

    public function create(Request $request){
        try{
            $validator = Validator::make(
                $request->all(),
                [
                    'product_id'    => 'required|exists:product,product_id',
                    'workcenter_id' => 'required|exists:workcenter,workcenter_id',
                    'bom_default'   => 'nullable|exists:bom_hd,bom_hd_id',
                    'created_by'    => 'required'
                ]
            );
    
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $validator->errors()]);
            } else {
                $this->productWorkcenter = DB::table("product_workcenter")
                    ->insert([
                        'product_id'    => $request->product_id,
                        'workcenter_id' => $request->workcenter_id,
                        'bom_default'   => $request->bom_default,
                        'created_by'    => $request->created_by
                    ]);
                
                if ($this->productWorkcenter == 0) {
                    return response()->json(['status' => 'error', 'message' => 'Failed to insert Product Workcenter, Contact your administrator']);
                } else {
                    return response()->json(['status' => 'ok', 'message' =>'Product workcenter has been inserted']);
                }
            }
        }catch(\Exception $e){
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function update(Request $request){
        try{
            $validator = Validator::make(
                $request->all(),
                [
                    'product_workcenter_id' => 'required',
                    'product_id'            => 'required|exists:product,product_id',
                    'workcenter_id'         => 'required|exists:workcenter,workcenter_id',
                    'bom_default'           => 'nullable|exists:bom_hd,bom_hd_id',
                    'updated_by'            => 'required',
                    'reason'                => 'required'
                ]
            );
    
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $validator->errors()]);
            } else {
                DB::beginTransaction();
                QueryController::reasonAction("update");
                $this->productWorkcenter = DB::table("product_workcenter")
                    ->where('product_workcenter_id', $request->product_workcenter_id)
                    ->update([
                        'product_id'    => $request->product_id,
                        'workcenter_id' => $request->workcenter_id,
                        'bom_default'   => $request->bom_default,
                        'updated_by'    => $request->updated_by
                    ]);
                
                if ($this->productWorkcenter == 0) {
                    DB::rollback();
                    return response()->json(['status' => 'error', 'message' => 'Failed to update Product Workcenter, Contact your administrator']);
                } else {
                    DB::commit();
                    return response()->json(['status' => 'ok', 'message' =>'Product workcenter has been updated']);
                }
            }
        }catch(\Exception $e){
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function destroy($id){
        try{
            DB::beginTransaction();
            QueryController::reasonAction("delete");
            $this->productWorkcenter = DB::table('product_workcenter')
                ->where('product_workcenter_id', $id)
                ->delete();

            if ($this->productWorkcenter == 0) {
                DB::rollback();
                return response()->json(['status' => 'error', 'message' => 'Failed to delete Product Workcenter, Contact your administrator']);
            } else {
                DB::commit();
                return response()->json(['status' => 'ok', 'message' => 'Product Workcenter has been deleted']);
            }
        }catch(\Exception $e){
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function check(Request $request){
        $product = $request->get('product');
        $workcenter = $request->get('workcenter');
        $validator = Validator::make($request->all(), [
            'product' => 'required',
            'workcenter' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->productWorkcenter = DB::table('product_workcenter')
                ->where('product_id', $product)
                ->where('workcenter_id', $workcenter)
                ->count();
            return response()->json(['status' => 'ok', 'message' => $this->productWorkcenter]);
        }
    }
}
