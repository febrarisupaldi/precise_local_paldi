<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\Helpers\QueryController;

class ProductDesignController extends Controller
{
    private $productDesign, $checkProductDesign;
    public function index()
    {
        $this->productDesign = DB::table('product_design as a')
            ->select(
                'design_id',
                'design_code as Kode desain',
                'design_name as Nama desain',
                'design_description as Keterangan',
                'appearance_name as Tampilan',
                'license_type_name as Jenis lisensi',
                DB::raw("concat(d.color_type_code, ' - ', d.color_type_name) as 'Tipe warna',
                    case a.is_active_sell
                        when 0 then 'Tidak aktif'
                        when 1 then 'Aktif' 
                    end as 'Status aktif jual'
                    , case a.is_active_production
                        when 0 then 'Tidak aktif'
                        when 1 then 'Aktif' 
                    end as 'Status aktif produksi'"),
                'a.created_on as Tanggal input',
                'a.created_by as User input',
                'a.updated_on as Tanggal update',
                'a.updated_by as User update'
            )->leftJoin('precise.product_appearance as b', 'a.appearance_id', '=', 'b.appearance_id')
            ->leftJoin('precise.product_license_type as c', 'a.license_type_id', '=', 'c.license_type_id')
            ->leftJoin('precise.color_type as d', 'a.color_type_id', '=', 'd.color_type_id')
            ->get();
        return response()->json(["data" => $this->productDesign]);
    }

    public function show($id)
    {
        $this->productDesign = DB::table('product_design as a')
            ->where('design_id', $id)
            ->select(
                'design_id',
                'design_code',
                'design_name',
                'design_description',
                'appearance_id',
                'license_type_id',
                'color_type_id',
                'is_active_sell',
                'is_active_production'
            )
            ->get();
        return response()->json(["data" => $this->productDesign]);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'design_code' => 'required|unique:product_design',
            'appearance_id' => 'required|exists:product_appearance,appearance_id',
            'color_type_id' => 'required|exists:color_type,color_type_id',
            'created_by' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->checkProductDesign = DB::table('product_design')
                ->insert([
                    'design_code' => $request->design_code,
                    'design_name' => $request->design_name,
                    'design_description' => $request->desc,
                    'appearance_id' => $request->appearance_id,
                    'license_type_id' => $request->license_type_id,
                    'color_type_id' => $request->color_type_id,
                    'created_by' => $request->created_by
                ]);

            if ($this->checkProductDesign == 0) {
                return response()->json(['status' => 'error', 'message' => 'Failed to insert new product design']);
            } else {
                return response()->json(['status' => 'ok', 'message' => 'product design has been inserted']);
            }
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'design_id' => 'required',
            'design_code' => 'required',
            'appearance_id' => 'required|exists:product_appearance,appearance_id',
            'color_type_id' => 'required|exists:color_type,color_type_id',
            'is_active_sell' => 'boolean',
            'is_active_production' => 'boolean',
            'updated_by' => 'required',
            'reason' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            DB::beginTransaction();
            try {
                $helper = new HelperController();
                $helper->reason("update");
                $this->checkProductDesign = DB::table('product_design')
                    ->where('design_id', $request->design_id)
                    ->update([
                        'design_code' => $request->design_code,
                        'design_name' => $request->design_name,
                        'desc' => $request->design_description,
                        'appearance_id' => $request->appearance_id,
                        'license_type_id' => $request->license_type_id,
                        'is_active_sell' => $request->is_active_sell,
                        'is_active_production' => $request->is_active_production,
                        'updated_by' => $request->updated_by
                    ]);

                if ($this->checkProductDesign == 0) {
                    DB::rollBack();
                    return response()->json(['status' => 'error', 'message' => 'Failed to update new product design']);
                } else {
                    DB::commit();
                    return response()->json(['status' => 'ok', 'message' => 'product design has been updated']);
                }
            } catch (\Exception $e) {
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
            $this->checkProductDesign = DB::table('product_design')
                ->where('design_id', $id)
                ->delete();

            if ($this->checkProductDesign == 0) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Failed to delete product design, Contact your administrator']);
            } else {
                DB::commit();
                return response()->json(['status' => 'ok', 'message' => 'Product design has been deleted']);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function check(Request $request)
    {
        $type = $request->get('type');
        $value = $request->get('value');
        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'value' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            if ($type == "code") {
                $this->checkProductDesign = DB::table('product_design')
                    ->where([
                        'design_code' => $value
                    ])->count();
            } else if ($type == "name") {
                $this->checkProductDesign = DB::table('product_design')
                    ->where([
                        'design_name' => $value
                    ])->count();
            }
            return response()->json(['status' => 'ok', 'message' => $this->checkProductDesign]);
        }
    }
}
