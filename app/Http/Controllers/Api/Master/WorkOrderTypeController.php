<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\Helpers\QueryController;

class WorkOrderTypeController extends Controller
{
    private $workOrderType;
    public function index(){
        $this->WorkOrderType = DB::table("precise.work_order_type")
            ->select(
                'work_order_type_code',
                'type_description',
                'created_on',
                'created_by',
                'updated_on',
                'updated_by'
            )
            ->get();

        return response()->json(["data" => $this->WorkOrderType]);
    }
}
