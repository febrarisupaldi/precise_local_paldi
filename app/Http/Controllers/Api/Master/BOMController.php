<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class BOMController extends Controller
{
    private $bom;

    public function show($id)
    {
        $this->bom = DB::table('bom_hd_id as s')
            ->where('bom_hd_id', $id)
            ->select(
                'a.bom_dt_id',
                'a.material_id',
                'b.product_code as Kode material',
                'b.product_name as Nama materal',
                'a.material_qty as Qty material',
                'a.material_uom as UOM material',
                'a.created_on as Tanggal input',
                'a.created_by as User input',
                'a.updated_on as Tanggal update',
                'a.updated_by as User update'
            )
            ->leftJoin('precise.product b', 'a.material_id', '=', 'b.product_id')
            ->get();
        return response()->json(['data' => $this->bom]);
    }

    public function showByWorkcenter($id)
    {
        $value = explode("-", $id);
        $this->bom = DB::table('precise.bom_hd as a')
            ->selectRaw("a.bom_hd_id, a.bom_code as 'Kode BOM', a.bom_name as 'Nama BOM', a.bom_description as 'Keterangan'
            , concat(d.workcenter_code,'-',d.workcenter_name) as 'Workcenter'
            , c.product_code as 'Kode barang', c.product_name as 'Nama barang', a.product_qty as 'Qty BOM', a.product_uom as 'UOM'
            , case a.is_active 
                when 0 then 'Tidak aktif'
                when 1 then 'Aktif'
              end as 'Status aktif'
            , a.start_date as 'Tanggal berlaku', a.expired_date as 'Tanggal kadaluarsa', a.usage_priority as 'Prioritas BOM'
            , a.created_on as 'Tanggal input', a.created_by as 'User input', a.updated_on as 'Tanggal update', a.updated_by as 'User update'")
            ->leftJoin('product_workcenter as b', 'a.product_id', '=', 'b.product_id')
            ->leftJoin('product as c', 'a.product_id', '=', 'c.product_id')
            ->leftJoin('workcenter as d', 'b.workcenter_id', '=', 'd.workcenter_id')
            ->whereIn('b.workcenter_id', $value)
            ->get();
        return response()->json(['data' => $this->bom]);
    }

    public function showByProduct($id)
    {
        $this->bom = DB::table('precise.bom_hd')
            ->where('product_id', $id)
            ->select(
                'bom_hd_id',
                'bom_code',
                'bom_name'
            )
            ->get();
        return response()->json(['data' => $this->bom]);
    }
}
