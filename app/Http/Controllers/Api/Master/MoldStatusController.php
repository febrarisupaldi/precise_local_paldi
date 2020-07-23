<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\Helpers\QueryController;

class MoldStatusController extends Controller
{
    private $moldStatus;
    public function index(){
        $this->moldStatus = DB::table('precise.mold_status')
            ->select(
                'status_code as Kode Status',
                'status_description as Keterangan',
                'created_on as Tanggal input',
                'created_by as User input',
                'updated_on as Tanggal update',
                'updated_by as User update'
            )
            ->get();

        return response()->json(["data"=>$this->moldStatus]);
    }
}
