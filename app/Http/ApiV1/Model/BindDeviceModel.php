<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/19
 * Time: 15:39
 */

namespace App\Http\ApiV1\Model;


use function core\convert_obj;

class BindDeviceModel extends Model
{
    protected $table = 'bind_device';

    public static function findByUserIdAndDevice(int $user_id , string $device_code)
    {
        $res = self::where([
            ['user_id' , '=' , $user_id] ,
            ['device_code' , '=' , $device_code] ,
        ])->first();
        $res = convert_obj($res);
        self::single($res);
        return $res;
    }
}