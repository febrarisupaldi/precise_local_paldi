<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DrivingLicenseController extends Controller
{
    private $drivingLicense;

    public function index()
    {
        $this->drivingLicense = DB::table('precise.xyz_driving_license')
            ->select(
                'driving_license_type_id',
                'driving_license_type_desc'
            )
            ->get();
        return response()->json(["data" => $this->drivingLicense]);
    }
}
