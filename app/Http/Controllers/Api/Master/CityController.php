<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\Helpers\QueryController;

class CityController extends Controller
{
    private $city, $checkCity;
    public function index()
    {
        $this->city = DB::table('city as c')
            ->join('state as s', 'c.state_id', '=', 's.state_id')
            ->join('country as co', 's.country_id', '=', 'co.country_id')
            ->select(
                'city_id',
                'city_code as Kode Kota',
                'city_name as Nama Kota',
                'state_name as Nama propinsi',
                'co.country_name as Negara',
                'c.created_on as Tanggal input',
                'c.created_by as User input',
                'c.updated_on as Tanggal update ',
                'c.updated_by as User update'
            )
            ->get();
        return response()->json(['data' => $this->city]);
    }

    public function show($id)
    {
        $this->city = DB::table('city')
            ->where('city_id', $id)
            ->select(
                'city_code',
                'city_name',
                'state_id'
            )
            ->get();
        return response()->json(['data' => $this->city]);
    }

    public function create(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'city_code' => 'required|unique:city',
                'city_name' => 'required',
                'state_id'  => 'required|exists:state,state_id',
                'created_by'=> 'required'
            ]
        );

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->checkCity = DB::table('city')
            ->insert([
                'city_code' => $request->city_code,
                'city_name' => $request->city_name,
                'state_id'  => $request->state_id,
                'created_by'=> $request->created_by
            ]);

            if ($this->checkCity == 0) {
                return response()->json(['status' => 'error', 'message' => 'Failed to insert city, Contact your administrator']);
            } else {
                return response()->json(['status' => 'ok', 'message' => $request->city_name . ' has been inserted']);
            }
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'city_id'   => 'required',
                'city_code' => 'required',
                'city_name' => 'required',
                'state_id'  => 'required',
                'updated_by'=> 'required',
                'reason'    => 'required'
            ]
        );

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            DB::beginTransaction();
            try {
                QueryController::reasonAction("update");
                $this->checkCity = DB::table('city')->where('city_id', $request->city_id)->update([
                    'city_code' => $request->city_code,
                    'city_name' => $request->city_name,
                    'state_id' => $request->state_id,
                    'updated_by' => $request->updated_by
                ]);

                if ($this->checkCity == 0) {
                    DB::rollback();
                    return response()->json(['status' => 'error', 'message' => 'Failed to update city, Contact your administrator']);
                } else {
                    DB::commit();
                    return response()->json(['status' => 'ok', 'message' => 'City has been updated']);
                }
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
            if ($type == "code") {
                $this->checkCity = DB::table('city')->where('city_code', $value)->count();
            } elseif ($type == "name") {
                $this->checkCity = DB::table('city')->where('city_name', $value)->count();
            }
            return response()->json(['status' => 'ok', 'message' => $this->checkCity]);
        }
    }
}
