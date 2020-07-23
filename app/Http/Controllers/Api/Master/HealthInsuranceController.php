<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class HealthInsuranceController extends Controller
{
    private $healthInsurance;
    public function index()
    {
        $this->healthInsurance = DB::table('precise.xyz_health_insurance')
            ->select(
                "health_insurance_code",
                "health_insurance_desc"
            )->get();
        return response()->json(["data" => $this->healthInsurance]);
    }
}
