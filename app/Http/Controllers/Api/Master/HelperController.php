<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Auth\AuthController;

class HelperController extends Controller
{
    public function test()
    {
        return "test";
    }

    public function reason($type)
    {
        $user  = new AuthController();
        //$me = $user->me()->getData();
        if ($type == "update") {
            $reason = DB::statement(
                "SET @userName=:user , @reason=:reason",
                array(':user' => request('updated_by'), ':reason' => request('reason'))
            );
        } else if ($type == "delete") {
            $reason = DB::statement(
                "SET @userName=:user , @reason=:reason",
                array(':user' => request('deleted_by'), ':reason' => request('reason'))
            );
        }
        return $reason;
    }

    public function insertOrUpdate(array $rows, $table, $deletedString)
    {
        //$table = \DB::getTablePrefix() . with(new self)->getTable();
        $first = reset($rows);

        $columns = implode(
            ',',
            array_map(function ($value) {
                return "$value";
            }, array_keys($first))
        );

        $values = implode(
            ',',
            array_map(function ($row) {
                return '(' . implode(
                    ',',
                    array_map(function ($value) {
                        return '"' . str_replace('"', '""', $value) . '"';
                    }, $row)
                ) . ')';
            }, $rows)
        );

        $updates = implode(
            ',',
            array_map(function ($value) {
                return "$value = VALUES($value)";
            }, array_keys($first))
        );
        if ($deletedString != '') {
            $updates = str_replace($deletedString, '', $updates);
        }
        $sql = "INSERT INTO {$table}({$columns}) VALUES {$values} ON DUPLICATE KEY UPDATE {$updates}";

        //return $sql;
        return DB::insert($sql);
    }
}
