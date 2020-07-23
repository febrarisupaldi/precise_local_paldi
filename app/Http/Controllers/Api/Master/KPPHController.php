<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class KPPHController extends Controller
{
    private $kpph;

    public function index()
    {
        $this->kpph = DB::table('precise.xyz_kpph')
            ->select(
                "kpph_id",
                "kpph_code",
                "kpph_desc"
            )->get();
        return response()->jsonA(["data" => $this->kpph]);
    }
}
