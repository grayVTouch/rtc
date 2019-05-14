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

class User extends Model
{
    protected $table = 'user';
    public $timestamps = false;

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
}