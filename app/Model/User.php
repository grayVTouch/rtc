<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/10
 * Time: 16:29
 */

namespace App\Model;


use App\Util\Misc;
use function core\random;
use Exception;

class User extends Model
{
    protected $table = 'user';
    public $timestamps = false;

    public static function single($m = null)
    {
        if (empty($m)) {
            return ;
        }
        if (!is_object($m)) {
            throw new Exception('参数 1 类型错误');
        }
    }

    public static function findByUniqueCode(string $unique_code = '')
    {
        $res = self::where('unique_code' , $unique_code)
            ->first();
        self::single($res);
        return $res;
    }

    public static function temp(string $identifier)
    {
        $data = [
            'identifier' => $identifier ,
            'username'   => 'username_' . random(6 , 'mixed' , true) ,
            'nickname'   => 'nickname_' . random(6 , 'mixed' , true) ,
            'avatar'     => '' ,
            'unique_code' => Misc::uniqueCode() ,
            'is_temp'    => 'y' ,
        ];
        $id = self::insertGetId($data);
        return self::findById($id);
    }

    // 创建临时后台用户
    public static function tempAdmin(string $identifier)
    {
        $data = [
            'identifier' => $identifier ,
            'username'   => '客服_' . random(6 , 'mixed' , true) ,
            'nickname'   => '客服_' . random(6 , 'mixed' , true) ,
            'avatar'     => '' ,
            'unique_code' => Misc::uniqueCode() ,
            'is_temp'    => 'y' ,
            'role'      => 'admin' ,
        ];
        $id = self::insertGetId($data);
        return self::findById($id);
    }

    public static function getByIdentifierAndRole(string $identifer = '' , string $role = '')
    {
        $res = self::where([
                    ['identifier' , '=' , $identifer] ,
                    ['role' , '=' , $role] ,
                ])
                ->get();
        self::multiple($res);
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

    public static function getIdByRole(string $role = '')
    {
        if (empty($role)) {
            $res = self::all();
        } else {
            $res = self::where('role' , $role)->get();
        }
        $id_list = [];
        foreach ($res as $v)
        {
            $id_list[] = $v->id;
        }
        return $id_list;
    }
}