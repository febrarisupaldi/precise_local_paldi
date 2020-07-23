<?php

namespace App\Http\Controllers\Api\Application;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ApplicationController extends Controller
{
    private $application, $checkApplication;
    public function index()
    { }

    public function autoIncrement(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'db' => 'required',
            'table' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->checkApplication = DB::table('information_schema.TABLES')
                ->select('AUTO_INCREMENT')
                ->where([
                    'TABLE_SCHEMA' => $request->db,
                    'TABLE_NAME' => $request->table
                ])
                ->get();
            return response()->json(['data' => $this->checkApplication]);
        }
    }

    public function serverTime()
    {
        $this->application = DB::select("select sysdate() as 'ServerTime'");
        return response()->json(['data' => $this->application]);
    }

    public function globalVariabel()
    {
        $this->application = DB::table('system_variable')
            ->select(
                'variable_name',
                DB::raw('value')
            )
            ->get();
        return response()->json(['data' => $this->application]);
    }

    public function error()
    {
        $this->application = DB::table('error_code_hd')
            ->select(
                'error_code',
                'help_link_local',
                'help_link_online'
            )
            ->get();
        return response()->json(['data' => $this->application]);
    }
}
