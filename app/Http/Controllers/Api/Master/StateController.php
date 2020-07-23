<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\Master\HelperController;

class StateController extends Controller
{
    private $state, $checkState;
    public function index()
    {
        $this->state = DB::table('state as s')
            ->select(
                'state_id',
                'state_code as Kode Propinsi',
                'state_name as Nama Propinsi',
                'country_name as Nama Negara',
                's.created_on as Tanggal Input',
                's.created_by as User Input',
                's.updated_on as Tanggal Update',
                's.updated_by as User Edit'
            )
            ->leftJoin('country as c', 's.country_id', '=', 'c.country_id')
            ->get();
        return response()->json(['data' => $this->state]);
    }
    public function show($id)
    {
        $this->state = DB::table('state')
            ->select(
                'state_code',
                'state_name',
                'country_id'
            )->where('state_id', $id)
            ->get();
        return response()->json(['data' => $this->state]);
    }
    public function create(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'state_code' => 'required|unique:state',
                'state_name' => 'required',
                'country_id' => 'required|exists:country,country_id',
                'created_by' => 'required'
            ]
        );
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {

            $this->checkState = DB::table('state')->insert([
                'state_code' => $request->state_code,
                'state_name' => $request->state_name,
                'country_id' => $request->country_id,
                'created_by' => $request->created_by
            ]);

            if ($this->checkState == 0) {
                return response()->json(['status' => 'error', 'message' => 'Failed to insert state, Contact your administrator']);
            } else {
                return response()->json(['status' => 'ok', 'message' => $request->state_name . ' State has been inserted']);
            }
        }
    }
    public function update(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'state_id'   => 'required',
                'state_code' => 'required',
                'state_name' => 'required',
                'country_id' => 'required|exists:country,country_id',
                'updated_by' => 'required',
                'reason' => 'required'
            ]
        );

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            try {
                DB::beginTransaction();
                $helper = new HelperController();
                $helper->reason("update");

                $this->checkState = DB::table('state')
                    ->where('state_id', $request->state_id)
                    ->update([
                        'state_code' => $request->state_code,
                        'state_name' => $request->state_name,
                        'country_id' => $request->country_id,
                        'updated_by' => $request->updated_by
                    ]);

                if ($this->checkState == 0) {
                    DB::rollback();
                    return response()->json(['status' => 'error', 'message' => 'Failed to update state, Contact your administrator']);
                } else {
                    DB::commit();
                    return response()->json(['status' => 'ok', 'message' => $request->state_name . ' State has been updated']);
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
                $state = DB::table('state')->where('state_code', $value)->count();
            } elseif ($type == "name") {
                $state = DB::table('state')->where('state_name', $value)->count();
            }
            return response()->json(['status' => 'ok', 'message' => $state]);
        }
    }
}
