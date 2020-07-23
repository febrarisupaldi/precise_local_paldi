<?php

namespace App\Http\Controllers\Api\Helpers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\Auth\AuthController;

class QueryController extends Controller
{
    public static function insertOrUpdate(array $rows, $table)
    {
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

        $sql = "INSERT INTO {$table}({$columns}) VALUES {$values} ON DUPLICATE KEY UPDATE {$updates}";
        return $sql;
    }

    public static function reason($data)
    {
        if($data['updated_by'] != null){
            $reason = DB::statement(
                "SET @userName=:user , @reason=:reason",
                array(':user' => $data['updated_by'], ':reason' => $data['reason'])
            );
        }else if($data['deleted_by'] != null){
            $reason = DB::statement(
                "SET @userName=:user , @reason=:reason",
                array(':user' => $data['deleted_by'], ':reason' => $data['reason'])
            );
        }
        return $reason;
    }

    public static function reasonAction($type){
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
}
