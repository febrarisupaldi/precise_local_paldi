<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CountryController extends Controller
{
    private $country, $checlCountry;
    public function index()
    {
        $this->country = DB::table('country')
            ->select(
                'country_id',
                'country_code as Kode Negara',
                'country_name as Nama Negara',
                'created_on as Tanggal Input',
                'created_by as User Input',
                'updated_on as Tanggal Update',
                'updated_by as User Edit'
            )
            ->get();
        return response()->json(['data' => $this->country]);
    }

    public function show($id)
    {
        $this->country = DB::table('country')
            ->where('country_id', $id)
            ->select(
                'country_code',
                'country_name'
            )
            ->get();
        return response()->json(['data' => $this->country]);
    }
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_code' => 'required|unique:country',
            'country_name' => 'required',
            'created_by' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {

            $this->checkCountry = DB::table('country')->insert([
                'country_code' => $request->country_code,
                'country_name' => $request->country_name,
                'created_by' => $request->created_by
            ]);

            if ($this->checkCountry == 0) {
                return response()->json(['status' => 'error', 'message' => 'Failed insert ' . $request->country_name . ' , contact your administrator']);
            } else {
                return response()->json(['status' => 'ok', 'message' => $request->country_name . ' was inserted']);
            }
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_id' => 'required',
            'country_code' => 'required',
            'country_name' => 'required',
            'updated_by' => 'required',
            'reason' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            DB::beginTransaction();
            try {
                $helper = new HelperController();
                $helper->reason("update");
                $this->checkCountry = DB::table('country')
                    ->where('country_id', $request->country_id)
                    ->update([
                        'country_code' => $request->country_code,
                        'country_name' => $request->country_name,
                        'updated_by' => $request->updated_by
                    ]);

                if ($this->checkCountry == 0) {
                    DB::rollBack();
                    return response()->json(['status' => 'error', 'message' => 'Failed to update country, Contact your administrator']);
                } else {
                    DB::commit();
                    return response()->json(['status' => 'ok', 'message' => 'Country has been updated']);
                }
            } catch (\Exception $e) {
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
                $this->checkCountry = DB::table('country')->where('country_code', $value)->count();
            } elseif ($type == "name") {
                $this->checkCountry = DB::table('country')->where('country_name', $value)->count();
            }
            return response()->json(['status' => 'ok', 'message' => $this->checkCountry]);
        }
    }
}
