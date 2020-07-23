<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class SalaryStatusController extends Controller
{
    private $salaryStatus;
    public function index()
    {
        $this->salaryStatus = DB::table('precise.xyz_salary_status')
            ->select(
                "salary_status_id",
                "salary_status_desc"
            )->get();
        return response()->json(["data" => $this->salaryStatus]);
    }
}
