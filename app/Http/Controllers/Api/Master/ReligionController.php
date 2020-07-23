<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ReligionController extends Controller
{
    private $religion;
    public function index()
    {
        $this->religion = DB::table('precise.xyz_religion')
            ->select(
                'religion_id',
                'religion_name'
            )->get();

        return response()->json(['data' => $this->religion]);
    }
}
