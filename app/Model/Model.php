<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/4/7
 * Time: 21:50
 */

namespace App\Model;

use Exception;
use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
    public $timestamps = false;

    public static function multiple($list)
    {
        foreach ($list as $v)
        {
            static::single($v);
        }
    }

    public static function single($m = null)
    {
        if (empty($m)) {
            return ;
        }
        if (!is_object($m)) {
            throw new Exception('参数 1 类型错误');
        }
    }

    // 更新
    public static function updateById(int $id , array $param = [])
    {
        return static::where('id' , $id)
            ->update($param);
    }

    public static function updateByIds(array $id_list = [] , array $param = [])
    {
        return static::whereIn('id' , $id_list)
            ->update($param);
    }

    public static function getAll()
    {

        $res = static::orderBy('id' , 'desc')
            ->get();
        static::multiple($res);
        return $res;
    }

    public static function findById(int $id)
    {
        $res = static::find($id);
        if (empty($res)) {
            return null;
        }
        static::single($res);
        return $res;
    }

    public static function getByIds(array $id_list = [])
    {
        $res = static::whereIn('id' , $id_list)->get();
        static::multiple($res);
        return $res;
    }

    // 检查是否全部存在
    public static function allExist(array $id_list = [])
    {
        $count = static::whereIn('id' , $id_list)->count();
        return count($id_list) == $count;
    }
}