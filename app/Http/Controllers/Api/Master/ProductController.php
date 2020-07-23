<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    private $product, $checkProduct;
    public function show($id)
    {
        $this->product = DB::table('product')->where('product_id', $id)->get();
        return response()->json(['data' => $this->product]);
    }

    public function showByProductGroup($id)
    {
        $id = explode("-", $id);
        $this->product = DB::table('product as a')->selectRaw("
                product_id, product_code 'Kode barang',
                product_name 'Nama barang', product_alias 'Alias',
                concat(c.product_type_code, ' - ', c.product_type_name) 'Tipe produk',
                concat(b.product_group_code, ' - ', b.product_group_name) 'Group produk',
                uom_code 'UOM',
                a.created_on 'Tanggal input',
                a.created_by 'User input',
                a.updated_on 'Tanggal update',
                a.updated_by 'User update'
        ")->leftJoin('product_group as b', 'a.product_group_id', '=', 'b.product_group_id')
            ->leftJoin('product_type as c', 'b.product_type_id', '=', 'c.product_type_id')
            ->whereIn('b.product_group_id', $id)
            ->orderBy('a.product_code')
            ->get();
        return response()->json(['data' => $this->product]);
    }

    public function showByProductType($id)
    {
        $id = explode("-", $id);
        $this->product = DB::table('product as a')->selectRaw("
                product_id,
                product_code 'Kode barang',
                product_name 'Nama barang',
                product_alias 'Alias',
                concat(c.product_type_code, ' - ', c.product_type_name) 'Tipe produk',
                concat(b.product_group_code, ' - ', b.product_group_name) 'Group produk',
                uom_code 'UOM',
                a.created_on 'Tanggal input',
                a.created_by 'User input',
                a.updated_on 'Tanggal update',
                a.updated_by 'User update'
        ")->leftJoin('product_group as b', 'a.product_group_id', '=', 'b.product_group_id')
            ->leftJoin('product_type as c', 'b.product_type_id', '=', 'c.product_type_id')
            ->whereIn('b.product_type_id', $id)
            ->orderBy('a.product_code')
            ->get();
        return response()->json(['data' => $this->product]);
    }
}
