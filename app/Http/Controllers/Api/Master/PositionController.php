<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PositionController extends Controller
{
    private $position;

    public function index()
    {
        $this->position = DB::table('precise.xyz_position')
            ->select(
                "position_id",
                "position_name",
                "level_code"
            )
            ->get();
        return response()->json(["data" => $this->position]);
    }
}
