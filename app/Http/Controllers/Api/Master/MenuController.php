<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{
    private $menu, $checkMenu;
    public function index()
    {
        $this->menu = DB::table('menu as m')->select(
            'menu_id as Menu ID',
            'menu_name as Nama menu',
            'menu_parent as Parent Menu ID',
            'm.menu_category_id as Category ID',
            'c.menu_category_name as Nama kategori',
            'is_active as Status aktif',
            'm.created_on as Tanggal input',
            'm.created_by as User input',
            'm.updated_on as Tanggal edit',
            'm.updated_by as User edit'
        )->leftJoin(
            'menu_category as c',
            'm.menu_category_id',
            '=',
            'c.menu_category_id'
        )->get();

        return response()->json(['data' => $this->menu]);
    }

    public function show($id)
    {
        $this->menu = DB::table('menu')
            ->where('menu_id', $id)
            ->select(
                'menu_name',
                'menu_parent',
                'menu_category_id',
                'is_active'
            )
            ->get();
        return response()->json(['data' => $this->menu]);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'menu_name' => 'required',
            'menu_category_id' => 'required|exists:menu_category,menu_category_id',
            'created_by' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()]);
        } else {
            $this->checkMenu = DB::table('menu')->insert([
                'menu_name' => $request->menu_name,
                'menu_parent' => $request->menu_parent,
                'menu_category_id' => $request->menu_category_id,
                'created_by' => $request->created_by
            ]);

            if ($this->checkMenu == 0) {
                return response()->json(['status' => 'error', 'message' => 'Failed to create menu']);
            } else {
                return response()->json(['status' => 'ok', 'message' => $request->menu_name . ' has been created']);
            }
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'menu_name' => 'required',
            'menu_category_id' => 'required|exists:menu_category,menu_category_id',
            'is_active' => 'boolean',
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
                $this->checkMenu = DB::table('menu')->where('menu_id', $request->menu_id)->update([
                    'menu_name' => $request->menu_name,
                    'menu_parent' => $request->menu_parent,
                    'is_active' => $request->is_active,
                    'menu_category_id' => $request->menu_category_id,
                    'updated_by' => $request->updated_by
                ]);

                if ($this->checkMenu == 0) {
                    DB::rollBack();
                    return response()->json(['status' => 'error', 'message' => 'Failed to update menu']);
                } else {
                    DB::commit();
                    return response()->json(['status' => 'ok', 'message' => 'Menu has been updated']);
                }
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $helper = new HelperController();
            $helper->reason("delete");
            $this->checkMenu = DB::table('menu')
                ->where('menu_id', $id)
                ->delete();

            if ($this->checkMenu == 1) {
                return response()->json(['status' => 'ok', 'message' => 'Menu has been deleted']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Failed to delete menu']);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
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
            if ($type == "menu") {
                $this->checkMenu = DB::table('menu')->where('menu_name', $value)->count();
            }
            return response()->json(['status' => 'ok', 'message' => $this->checkMenu]);
        }
    }
}
