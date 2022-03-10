<?php
namespace App\Http\Traits;

/**
 * Created by PhpStorm.
 * User: hosseini
 * Date: 1/27/18
 * Time: 12:43 PM
 */
use Illuminate\Support\Facades\DB;

trait EnumValueTrait
{
    public static function getEnumValues($table_name)
    {
        $fields = DB::connection((new static)->connection)->select(
            DB::raw("SHOW COLUMNS FROM ".$table_name."")
        );
        $result = [];
        foreach ($fields as $field) {
            $enum = self::parsEnumValues($field->Type);
            if (!empty($enum))
                $result[$field->Field] = $enum;
        }
       
        return $result; 

    }

    private static function parsEnumValues($type)
    {
        preg_match('/^enum\((.*)\)$/', $type, $matches);
        $enum = array();
        if (empty($matches))
            return null;

        foreach (explode(',', $matches[1]) as $value) {
            $v = trim($value, "'");
            $enum = array_add($enum, $v, $v);
        }
        return $enum;
    }
}