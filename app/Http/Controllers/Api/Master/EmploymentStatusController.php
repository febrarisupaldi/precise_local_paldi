<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class EmploymentStatusController extends Controller
{
    private $employmentStatus;
    public function index()
    {
        $this->employmentStatus = DB::table('precise.xyz_employment_status')
            ->select(
                "employment_status_id",
                "employment_status_name"
            )->get();

        return response()->json(["data" => $this->employmentStatus]);
    }
}
