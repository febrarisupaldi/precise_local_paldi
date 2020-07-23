<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ProductLicenseTypeController extends Controller
{
    private $productLicenseType, $checkProductLicenseType;
    public function index()
    {
        $this->productLicenseType = DB::table('product_license_type')
            ->select('license_type_id', 'license_type_name')
            ->get();
        return response()->json(["data" => $this->productLicenseType]);
    }
}
