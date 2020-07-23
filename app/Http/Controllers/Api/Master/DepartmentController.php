<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    private $department;
    public function index()
    {
        $this->department = DB::table("precise.xyz_department")
            ->select(
                "department_id",
                "department_name"
            )
            ->get();

        return response()->json(["data" => $this->department]);
    }
}
