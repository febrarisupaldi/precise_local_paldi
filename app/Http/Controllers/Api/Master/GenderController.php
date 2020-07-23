<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class GenderController extends Controller
{
    private $gender;
    public function index()
    {
        $this->gender = DB::table('precise.xyz_gender')
            ->select(
                'gender_code',
                'gender_desc'
            )
            ->get();
        return response()->json(["data" => $this->gender]);
    }
}
