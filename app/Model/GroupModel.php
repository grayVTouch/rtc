<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/12
 * Time: 10:02
 */

namespace App\Model;


use function core\random;

class GroupModel extends Model
{
    protected $table = 'group';

    public static function single($m = null)
    {
        if (empty($m)) {
            return ;
        }
        if (!is_object($m)) {
            throw new Exception('参数 1 类型错误');
        }
        $m->image_explain = empty($m->image) ? config('app.group_image') : res_url($m->image);
    }

    public static function temp(int $user_id)
    {
        $data = [
            'user_id'   => $user_id ,
            'name'     => '【游客】advoise_' . random(6 , 'mixed' , true) ,
            'is_temp'    => 1 ,
            'is_service'    => 1 ,
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
            ['is_temp' , '<=' , 1] ,
        ])->get();
        self::multiple($res);
        return $res;
    }

    public static function serviceGroup()
    {
        $res = self::where('is_service' , 1)
            ->get();
        self::multiple($res);
        return $res;
    }

    public static function advoiseGroupByUserId(int $user_id)
    {
        $res = self::where([
                ['user_id' , '=' , $user_id] ,
                ['is_service' , '=' , 1] ,
            ])
            ->first();
        self::single($res);
        return $res;
    }
}