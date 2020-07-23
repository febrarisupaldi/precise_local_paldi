<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UnitController extends Controller
{
    private $unit;
    public function index()
    {
    }

    public function showBySection($id)
    {
        $this->unit = DB::table('precise.xyz_unit')
            ->where('section_id', $id)
            ->select(
                "unit_id",
                "unit_name"
            )
            ->get();
        return response()->json(["data" => $this->unit]);
    }
}
