<?php

namespace App\Http\Controllers\Api\Application;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\Auth\AuthController;

class SettingController extends Controller
{
    private $setting, $checkSetting;
    public function index()
    { }

    public function access()
    {
        $user  = new AuthController();
        $me = $user->me()->getData();

        $this->setting = DB::select('select menu.menu_id, menu.menu_name
                , ifnull(can_read, 0) can_read
                , ifnull(can_create, 0) can_create
                , ifnull(can_update, 0) can_update
                , ifnull(can_delete, 0) can_delete
                , ifnull(can_print, 0) can_print
                , ifnull(can_double_print, 0) can_double_print
                , ifnull(can_approve, 0) can_approve
        from precise.menu
        left join (
            select menu_id, can_read, can_create, can_update, can_delete, can_print, can_double_print, can_approve
            from precise.privilege
            where user_id = ?
        ) GivenAccess on menu.menu_id = GivenAccess.menu_id
        where menu.is_active = 1', [$me->data->user_id]);
        return response()->json(["data" => $this->setting]);
    }

    public function logging()
    {
        $this->setting = DB::table('system_variable')
            ->select(
                'variable_name',
                DB::raw("if(`value` = '1', true, false) `value`")
            )->whereIn('variable_name', [
                'desktop_enable_logging_menu',
                'desktop_enable_logging_login',
                'desktop_enable_logging_menu_action',
                'desktop_enable_logging_loading_time'
            ])->get();
        return response()->json(["data" => $this->setting]);
    }


    public function version()
    {
        $this->setting = DB::table('precise_release')
            ->select(
                'release_id',
                'release_date',
                'app_version',
                'app_major',
                'app_minor',
                'app_revision',
                'release_note',
                'release_link_local',
                'release_link_online',
                'file_name'
            )
            ->orderBy('app_major', 'desc')
            ->orderBy('app_minor', 'desc')
            ->orderBy('app_revision', 'desc')
            ->limit(1)->get();
        return response()->json(['data' => $this->setting]);
    }

    public function warehouse()
    {
        $user  = new AuthController();
        $me = $user->me()->getData();
        $this->setting = DB::table('privilege_warehouse as a')
            ->select(
                'a.warehouse_id',
                'b.warehouse_code',
                'b.warehouse_name',
                'a.privilege_type'
            )
            ->leftJoin('precise.warehouse as b', 'a.warehouse_id', '=', 'b.warehouse_id')
            ->where('a.user_id', $me->data->user_id)
            ->get();
        return response()->json(["data" => $this->setting]);
    }

    public function user()
    {
        $user  = new AuthController();
        $me = $user->me()->getData();
        $this->setting = DB::table('users as u')
            ->where('u.user_id', $me->data->user_id)
            ->select(
                'user_id',
                'employee_name',
                'email_internal',
                'email_external',
                'u.is_active'
            )->leftJoin('employee as e', 'u.user_id', '=', 'e.employee_nik')
            ->get();
        return response()->json(["data" => $this->setting]);
    }

    public function workcenter()
    {
        $user  = new AuthController();
        $me = $user->me()->getData();

        $this->setting = DB::table('privilege_workcenter as a')
            ->select(
                'a.workcenter_id',
                'b.workcenter_code',
                'b.workcenter_name'
            )
            ->leftJoin('workcenter as b', 'a.workcenter_id', '=', 'b.workcenter_id')
            ->where('a.user_id', $me->data->user_id)
            ->orderBy('workcenter_code')
            ->get();

        return response()->json(["data" => $this->setting]);
    }
}
