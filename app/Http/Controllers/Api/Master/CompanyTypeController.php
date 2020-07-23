<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CompanyTypeController extends Controller
{
    private $companyType, $checkCompanyType;
    public function index()
    {
        $this->companyType = DB::table('company_type')->select(
            'company_type_id',
            'company_type_code as Tipe perusahaan',
            'company_type_description as Keterangan',
            'created_on as Tanggal input',
            'created_by as User input',
            'updated_on as Tanggal update',
            'updated_by as User update'
        )->get();
        return response()->json(['data' => $this->companyType]);
    }

    public function show($id)
    {
        $this->companyType = DB::table('company_type')
            ->where('company_type_id', $id)
            ->select('company_type_code', 'company_type_description')
            ->get();
        return response()->json(['data' => $this->companyType]);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_type_code' => 'required|unique:company_type',
            'desc' => 'required',
            'created_by' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->checkCompanyType = DB::table('company_type')->insert([
                'company_type_code' => $request->company_type_code,
                'company_type_description' => $request->desc,
                'created_by' => $request->created_by
            ]);

            if ($this->checkCompanyType == 0) {
                return response()->json(['status' => 'error', 'message' => 'Failed to create Company Type, Contact your administrator']);
            } else {
                return response()->json(['status' => 'ok', 'message' => $request->company_type_code . ' has been created']);
            }
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_type_id' => 'required',
            'company_type_code' => 'required',
            'desc' => 'required',
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

                $this->checkCompanyType = DB::table('company_type')->where('company_type_id', $request->company_type_id)->update([
                    'company_type_code' => $request->company_type_code,
                    'company_type_description' => $request->desc,
                    'updated_by' => $request->updated_by
                ]);

                if ($this->checkCompanyType == 0) {
                    DB::rollBack();
                    return response()->json(['status' => 'error', 'message' => 'Failed to update Company Type, Contact your administrator']);
                } else {
                    DB::commit();
                    return response()->json(['status' => 'ok', 'message' => $request->company_type_code . ' has been updated']);
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
            if ($type == 'code') {
                $this->checkCompanyType = DB::table('company_type')->where('company_type_code', $value)->count();
            }
            return response()->json(['status' => 'ok', 'message' => $this->checkCompanyType]);
        }
    }
}
