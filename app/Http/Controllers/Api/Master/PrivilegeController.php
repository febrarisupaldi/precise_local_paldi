<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Master\HelperController;

class PrivilegeController extends Controller
{
    private $privilege, $checkPrivilege;
    public function index()
    {
        $this->privilege = DB::table('privilege as a')->select(
            'privilege_id',
            'user_id as User ID',
            'c.employee_name as User Name',
            'a.menu_id',
            'b.menu_name as Nama Menu',
            'b.menu_parent',
            'can_read as Can READ',
            'can_create as Can CREATE',
            'can_update as Can UPDATE',
            'can_delete as Can DELETE',
            'can_print as Can PRINT',
            'can_double_print as Can DOUBLE PRINT',
            'can_approve as Can APPROVE'
        )->leftJoin('menu as b', 'a.menu_id', '=', 'b.menu_id')
            ->leftJoin('employee as c', 'a.user_id', '=', 'c.employee_nik')
            ->get();
        return response()->json(["data" => $this->privilege]);
    }

    public function showMenuByUser($id)
    {
        $this->privilege = DB::select("select a.menu_id 'Menu ID', menu_name 'Nama menu', menu_parent 'Parent Menu ID'
        , b.menu_category_name 'Nama kategori'
        , can_read 'Can READ', can_create 'Can CREATE', can_update 'Can UPDATE', can_delete 'Can DELETE'
        , can_print 'Can Print', can_double_print 'Can DOUBLE PRINT', can_approve 'Can APPROVE'
        from precise.menu a
        left join precise.menu_category b on a.menu_category_id = b.menu_category_id
        left join (
            select privilege_id, menu_id, can_read, can_create, can_update, can_delete, can_print, can_double_print, can_approve
            from precise.privilege
            where user_id = ?
        ) p on a.menu_id = p.menu_id
        where is_active = 1
        ;", [1, $id]);

        return response()->json(["data" => $this->privilege]);
    }

    public function showUserByMenu($id)
    {
        $this->checkPrivilege = DB::table('privilege')
            ->where('menu_id', $id)->count();

        if ($this->checkPrivilege == 0)
            $this->privilege = DB::select(
                "select null as 'User ID', null as 'Can READ', null as 'Can CREATE', null as 'Can UPDATE', null as 'Can DELETE', null as 'Can PRINT', null as 'Can DOUBLE PRINT', null as 'Can APPROVE'
                from dual"
            );
        else
            $this->privilege = DB::table('privilege as p')
                ->select(
                    'user_id as User ID',
                    'can_read as Can READ',
                    'can_create as Can CREATE',
                    'can_update as Can UPDATE',
                    'can_delete as Can DELETE',
                    'can_print as Can PRINT',
                    'can_double_print as Can DOUBLE PRINT',
                    'can_approve as Can APPROVE'
                )->leftJoin('employee as e', 'p.user_id', '=', 'e.employee_nik')
                ->leftJoin('menu as m', 'p.menu_id', '=', 'm.menu_id')
                ->where('p.menu_id', $id)
                ->get();
        return response()->json(["data" => $this->privilege]);
    }

    public function user()
    {
        $this->privilege = DB::select("select p.user_id 'User ID', e.employee_name 'User Name'
        from (
            select user_id
            from precise.privilege
            group by user_id
        ) p left join precise.employee e on p.user_id = e.employee_nik
        ;");
        return response()->json(["data" => $this->privilege]);
    }

    public function create(Request $request)
    {
        $helper = new HelperController();

        if ($helper->insertOrUpdate($request->data, 'privilege', "'user_id = VALUES(user_id),menu_id = VALUES(menu_id),'") == false) {
            return response()->json(["status" => "error", "message" => "Failed to add privilege"]);
        } else {
            return response()->json(["status" => "ok", "message" => "Privilege has been added"]);
        }
    }

    public function copy(Request $request)
    {
        $helper = new HelperController();
        $query = DB::table('privilege')
            ->where('user_id', $request->user_id_from)
            ->selectRaw("
                '$request->user_id_to',
                menu_id,
                can_read,
                can_create,
                can_update,
                can_delete,
                can_print,
                can_double_print,
                can_approve,
                '$request->created_by'
            ");
        $this->privilege = DB::table('privilege as a')
            ->where('a.user_id', $request->user_id_to)
            ->whereNull('b.privilege_id')
            ->selectRaw("
                '$request->user_id_to',
                a.menu_id,
                a.can_read,
                a.can_create,
                a.can_update,
                a.can_delete,
                a.can_print,
                a.can_double_print,
                a.can_approve,
                '$request->created_by'
            ")->leftJoin(
                'privilege as b',
                function ($join) use ($request) {
                    $join->on('a.menu_id', '=', 'b.menu_id')
                        ->where('b.user_id', '=', $request->user_id_from);
                }
            )->union($query)->get();
        if (is_null($helper->insertOrUpdate(json($this->privilege), 'privilege', "'user_id = VALUES(user_id),menu_id = VALUES(menu_id),'"))) {
            return response()->json(["status" => "error", "message" => "Failed to copy privilege"]);
        } else {
            return response()->json(["status" => "ok", "message" => "Privilege has been copied"]);
        }
    }
}
