<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/19
 * Time: 11:01
 */

namespace App\Model;


class SmsCodeModel extends Model
{
    protected $table = 'sms_code';

    public static function findByIdentifierAndAreaCodeAndPhoneAndType(string $identifier , string $area_code , string $phone , int $type)
    {
        $res = self::where([
                ['identifier' , '=' , $identifier] ,
                ['area_code' , '=' , $area_code] ,
                ['phone' , '=' , $phone] ,
                ['type' , '=' , $type] ,
            ])
            ->first();
        self::single($res);
        return $res;
    }
}