<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2020/1/6
 * Time: 15:40
 */

namespace App\Model;


class SystemParamModel extends Model
{
    protected $table = 'system_param';

    public static function getValueByKey(string $key)
    {
        return self::where('key' , $key)
            ->value('value');
    }

    public static function findByKey(string $key)
    {
        $res = self::where('key' , $key)
            ->first();
        if (empty($res)) {
            return ;
        }
        self::single($res);
        return $res;
    }
}