<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    private $employee, $checkEmployee;
    public function index()
    {
        $this->employee = DB::table('precise.xyz_employee as empl')
            ->select(
                'employee_nik',
                'employee_id',
                'employee_name',
                'employee_npwp',
                'empl.driving_license_type_id',
                'driving_license_number',
                'gender_desc',
                'religion_name',
                'department_name',
                'section_name',
                'unit_name',
                'employee_position',
                'employee_level',
                'branch_name',
                'employee_city_birthday',
                'employee_date_birthday',
                'marital_status_name',
                'employee_couple',
                'employee_children_total',
                'employee_children',
                'kpph_code',
                'salary_status_desc',
                'employment_status_name',
                'shift_name',
                'employee_phone',
                'empl.blood_type_id',
                'bank_name',
                'bank_account_number',
                'employee_address',
                'employee_address2',
                'degree_name',
                'employee_graduate_year',
                'employee_education',
                'employee_education_major',
                'health_insurance_desc',
                'employee_employment_insurance_number',
                'employee_insurance',
                'employee_image',
                'employee_first_date_work',
                'employee_experience',
                'empl.is_active',
                'empl.created_on',
                'empl.created_by',
                'empl.updated_on',
                'empl.updated_by'
            )
            ->join('precise.xyz_department as dept', 'empl.department_id', '=', 'dept.department_id')
            ->leftJoin('precise.xyz_section as sect', 'sect.department_id', '=', 'dept.department_id')
            ->leftJoin('precise.xyz_unit as unit', 'unit.section_id', '=', 'sect.section_id')
            ->leftJoin('precise.xyz_driving_license as driv', 'empl.driving_license_type_id', '=', 'driv.driving_license_type_id')
            ->join('precise.xyz_branch as bran', 'empl.branch_id', '=', 'bran.branch_id')
            ->join('precise.xyz_gender as gend', 'empl.gender_code', '=', 'gend.gender_code')
            ->leftJoin('precise.xyz_religion as reli', 'empl.religion_id', '=', 'reli.religion_id')
            ->join('precise.xyz_marital_status as mari', 'empl.marital_status_id', '=', 'mari.marital_status_id')
            ->join('precise.xyz_kpph as kpph', 'kpph.kpph_id', '=', 'empl.kpph_id')
            ->join('precise.xyz_salary_status as sala', 'sala.salary_status_id', '=', 'empl.salary_status_id')
            ->join('precise.xyz_employment_status as emps', 'emps.employment_status_id', '=', 'empl.employment_status_id')
            ->join('precise.xyz_shift as shif', 'empl.shift_id', '=', 'shif.shift_id')
            ->leftJoin('precise.xyz_blood_type as bloo', 'empl.blood_type_id', '=', 'bloo.blood_type_id')
            ->join('precise.xyz_bank as bank', 'empl.bank_id', '=', 'bank.bank_id')
            ->join('precise.xyz_degree as degr', 'degr.degree_id', '=', 'empl.degree_id')
            ->join('precise.xyz_health_insurance as heal', 'heal.health_insurance_code', '=', 'empl.health_insurance_code')
            ->get();
        return response()->json(["data" => $this->employee]);
    }

    public function create(Request $request)
    {
    }

    public function check(Request $request)
    {
        $type = $request->get('type');
        $value = $request->get('value');
        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'value' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            if ($type == 'nik') {
                $this->employee = DB::table('precise.xyz_employee')
                    ->where('employee_id', $value)
                    ->count();
            }
            return response()->json(['status' => 'ok', 'message' => $this->employee]);
        }
    }
}
