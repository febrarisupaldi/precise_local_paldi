<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SectionController extends Controller
{
    private $section;
    public function index()
    {
    }

    public function show($id)
    {
        $this->section = DB::table('precise.xyz_section')
            ->select("")
            ->get();

        return response()->json(["data" => $this->section]);
    }

    public function showByDepartment($id)
    {

        $this->section = DB::table('precise.xyz_section as sect')
            ->where("sect.department_id", $id)
            ->select(
                "sect.section_id",
                "sect.section_name",
                "dept.department_name"
            )
            ->join("precise.xyz_department as dept", "sect.department_id", "=", "dept.department_id")
            ->get();

        return response()->json(["data" => $this->section]);
    }
}
