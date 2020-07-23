<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class BankController extends Controller
{
    private $bank;
    public function index()
    {
        $this->bank = DB::table('precise.xyz_bank')
            ->select(
                "bank_id",
                "bank_name"
            )->get();
        return response()->json(["data" => $this->bank]);
    }
}
