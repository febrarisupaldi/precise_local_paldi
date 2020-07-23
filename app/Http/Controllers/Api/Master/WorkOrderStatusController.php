<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\Helpers\QueryController;

class WorkOrderStatusController extends Controller
{
    private $workOrderStatus;
    public function index(){
        $this->workOrderStatus = DB::table('precise.work_order_status')
            ->select(
                'work_order_status_code',
                'status_description',
                'created_on',
                'created_by',
                'updated_on',
                'updated_by'
            )
            ->get();

        return response()->json(["data" => $this->workOrderStatus]);
    }
}
