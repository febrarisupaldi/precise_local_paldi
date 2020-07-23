<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ExtensionController extends Controller
{
    private $extension, $checkExtension;
    public function index()
    {
        $this->extension = DB::table('extension')->select(
            'extension_name as Nama extension',
            'extension_description as Keterangan',
            DB::raw("if(is_active = 1, 'Aktif', 'Tidak aktif') 'Status aktif'"),
            'created_on as Diinput tanggal',
            'created_by as Diinput oleh',
            'updated_on as Diubah tanggal',
            'updated_by as Diubah oleh'
        )->get();
        return response()->json(['data' => $this->extension]);
    }

    public function show($id)
    {
        $this->extension = DB::table('extension')
            ->where('extension_name', $id)
            ->select('extension_name', 'extension_description', 'is_active')
            ->get();
        return response()->json(['data' => $this->extension]);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'extension_name' => 'required|unique:extension',
            'desc' => 'required',
            'created_by' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $checkExtension = DB::table('extension')->insert([
                'extension_name' => $request->extension_name,
                'extension_description' => $request->desc,
                'created_by' => $request->created_by
            ]);

            if ($checkExtension == 0) {
                return response()->json(['status' => 'error', 'message' => 'Failed to insert Extension']);
            } else {
                return response()->json(['status' => 'ok', 'message' => $request->extension_name . ' has been inserted']);
            }
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'extension_name' => 'required',
            'desc' => 'required',
            'updated_by' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $checkExtension = DB::table('extension')->where('extension_name', $id)->update([
                'extension_name' => $request->extension_name,
                'extension_description' => $request->desc,
                'updated_by' => $request->updated_by
            ]);

            if ($checkExtension == 0) {
                return response()->json(['status' => 'error', 'message' => 'Failed to update Extension']);
            } else {
                return response()->json(['status' => 'ok', 'message' => $request->extension_name . ' has been update']);
            }
        }
    }

    public function destroy($id)
    {
        $checkExtension = DB::table('extension')->where('extension_name', $id)->delete();
        if ($checkExtension == 1) {
            return response()->json(['status' => 'ok', 'message' => 'Extension has been deleted']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Failed to delete extension']);
        }
    }

    public function check($type, $val)
    {
        if ($type == 'name') {
            $checkExtension = DB::table('color_type')->where('extension_name', $val)->count();
        }
        return response()->json(['status' => 'ok', 'message' => $checkExtension]);
    }
}
