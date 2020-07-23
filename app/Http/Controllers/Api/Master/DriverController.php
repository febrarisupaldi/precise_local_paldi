<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\Master\HelperController;

class DriverController extends Controller
{
    private $driver, $checkDriver;
    public function index()
    {
        $this->driver = DB::table('precise.driver as d')
            ->select(
                'd.driver_nik as NIK',
                'e.employee_name as Nama driver',
                DB::raw(
                    "case d.is_active 
                    when 0 then 'Tidak aktif'
                    when 1 then 'Aktif' 
                end as 'Status aktif'"
                ),
                'd.created_on as Tanggal input',
                'd.created_by as User input',
                'd.updated_on as Tanggal update',
                'd.updated_by as User update'
            )
            ->leftJoin('precise.employee as e', 'd.driver_nik', '=', 'e.employee_nik')
            ->get();

        return response()->json(["data" => $this->driver]);
    }

    public function show($id)
    {
        $this->driver = DB::table('precise.driver as d')
            ->where('d.driver_nik', $id)
            ->select(
                'd.driver_nik',
                'e.employee_name as driver_name',
                'd.is_active'
            )
            ->leftJoin('precise.employee as e', 'd.driver_nik', '=', 'e.employee_nik')
            ->get();

        return response()->json(["data" => $this->driver]);
    }

    public function create(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'driver_nik' => 'required|unique:driver|exists:employee,employee_nik',
                'created_by' => 'required'
            ]
        );
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->checkDriver = DB::table('precise.driver')
                ->insert([
                    'driver_nik' => $request->driver_nik,
                    'created_by' => $request->created_by
                ]);

            if ($this->checkDriver == 0) {
                return response()->json(['status' => 'error', 'message' => 'Failed to insert driver, Contact your administrator']);
            } else {
                return response()->json(['status' => 'ok', 'message' => $request->driver_nik . ' has been inserted']);
            }
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'driver_nik' => 'required|exists:employee,employee_nik',
                'updated_by' => 'required',
                'is_active' => 'boolean',
                'reason' => 'required'
            ]
        );

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            DB::beginTransaction();
            try {
                $helper = new HelperController();
                $helper->reason("update");

                $this->checkDriver = DB::table('precise.driver')
                    ->where('driver_nik', $request->driver_nik)
                    ->update([
                        'is_active' => $request->is_active,
                        'updated_by' => $request->updated_by
                    ]);
                DB::commit();
                return response()->json(['status' => 'ok', 'message' => 'City has been updated']);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
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
            if ($type == "nik") {
                $this->checkDriver = DB::table('driver')->where('driver_nik', $value)->count();
            }
            return response()->json(['status' => 'ok', 'message' => $this->checkDriver]);
        }
    }
}
