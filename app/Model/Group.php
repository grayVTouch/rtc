<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/12
 * Time: 10:02
 */

namespace App\Model;


use function core\random;

class Group extends Model
{
    protected $table = 'group';
    public $timestamps = false;

    public static function temp(string $identifier)
    {
        $data = [
            'identifier' => $identifier ,
            'name'     => 'group(test)_' . random(6 , 'mixed' , true) ,
            'is_temp'    => 'y' ,
            'is_service'    => 'y' ,
        ];
        $id = self::insertGetId($data);
        return self::findById($id);
    }

    public static function findByName(string $name = '')
    {
        $res = self::where('name' , $name)->first();
        self::single($res);
        return $res;
    }

    public static function getTempByTimestamp(string $timestamp)
    {
        $res = self::where([
            ['create_time' , '<=' , $timestamp] ,
            ['is_temp' , '<=' , 'y'] ,
        ])->get();
        self::multiple($res);
        return $res;
    }

    public static function serviceGroup()
    {
        $res = self::where('is_service' , 'y')
            ->get();
        self::multiple($res);
        return $res;
    }
}