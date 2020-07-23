<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\Master\HelperController;

class VehicleController extends Controller
{
    private $vehicle, $checkVehicle;
    public function index()
    {
        $this->vehicle = DB::table('precise.vehicle')
            ->select(
                'vehicle_id',
                'vehicle_model as Model kendaraan',
                'license_number as Nomor plat',
                'vehicle_description as Keterangan',
                DB::raw("
                    case is_owned
                        when 0 then 'Tidak'
                        when 1 then 'Ya' 
                    end as 'Milik PC',
                    case is_active 
                        when 0 then 'Tidak aktif'
                        when 1 then 'Aktif' 
                    end as 'Status aktif'
                "),
                'created_on as Tanggal input',
                'created_by as User input',
                'updated_on as Tanggal update',
                'updated_by as User update'
            )->get();

        return response()->json(["data" => $this->vehicle]);
    }

    public function show($id)
    {
        $this->vehicle = DB::table('precise.vehicle')
            ->where('vehicle_id', $id)
            ->select(
                'vehicle_model',
                'license_number',
                'vehicle_description',
                'is_active',
                'is_owned'
            )->get();

        return response()->json(["data" => $this->vehicle]);
    }

    public function create(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'vehicle_model' => 'required',
                'license_number'=> 'required',
                'is_owned'      => 'required|boolean',
                'created_by'    => 'required'
            ]
        );
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->checkVehicle = DB::table('precise.vehicle')
                ->insert([
                    'vehicle_model'         => $request->vehicle_model,
                    'license_number'        => $request->license_number,
                    'vehicle_description'   => $request->desc,
                    'is_owned'              => $request->is_owned,
                    'created_by'            => $request->created_by,
                ]);

            if ($this->checkVehicle == 0) {
                return response()->json(['status' => 'error', 'message' => 'Failed to insert vehicle, Contact your administrator']);
            } else {
                return response()->json(['status' => 'ok', 'message' => 'Vehicle has been inserted']);
            }
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'vehicle_id' => 'required|exists:vehicle,vehicle_id',
                'vehicle_model' => 'required',
                'license_number' => 'required',
                'is_active' => 'required|boolean',
                'is_owned' => 'required|boolean',
                'updated_by' => 'required',
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

                $this->checkVehicle = DB::table('precise.vehicle')
                    ->where('vehicle_id', $request->vehicle_id)
                    ->update([
                        'vehicle_model'         => $request->vehicle_model,
                        'license_number'        => $request->license_number,
                        'vehicle_description'   => $request->desc,
                        'is_owned'              => $request->is_owned,
                        'is_active'             => $request->is_active,
                        'updated_by'            => $request->updated_by
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
            if ($type == "license_number") {
                $this->checkVehicle = DB::table('precise.vehicle')->where('license_number', $value)->count();
            }
            return response()->json(['status' => 'ok', 'message' => $this->checkVehicle]);
        }
    }
}
