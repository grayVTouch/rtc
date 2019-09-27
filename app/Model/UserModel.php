<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/10
 * Time: 16:29
 */

namespace App\Model;


use App\Util\MiscUtil;
use function core\convert_obj;
use function core\random;
use Exception;
use Illuminate\Support\Facades\DB;

class UserModel extends Model
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
        $m->avatar = empty($m->avatar) ? config('app.avatar') : $m->avatar;
    }

    public static function findByUniqueCode(string $unique_code = ''): ?UserModel
    {
        $res = self::where('unique_code' , $unique_code)
            ->first();
        self::single($res);
        return $res;
    }

    public static function findByUsername(string $username = ''): ?UserModel
    {
        $res = self::where('username' , $username)
            ->first();
        self::single($res);
        return $res;
    }

    public static function findByIdentifierAndUsername(string $identifier = '' , string $username = ''): ?UserModel
    {
        $res = self::where([
                ['identifier' , '=' , $identifier] ,
                ['username' , '=' , $username] ,
            ])
            ->first();
        self::single($res);
        return $res;
    }

    public static function temp(string $identifier)
    {
        try {
            DB::beginTransaction();
            $data = [
                'identifier' => $identifier ,
                'username'   => 'username_' . random(6 , 'mixed' , true) ,
                'unique_code' => MiscUtil::uniqueCode() ,
                'is_temp'    => 1 ,
                'role'      => 'user' ,
            ];
            $id = self::insertGetId($data);
            UserInfoModel::insert([
                'user_id'  => $id ,
                'nickname' => 'nickname_' . random(6 , 'mixed' , true)
            ]);
            DB::commit();
            return self::findById($id);
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // 创建临时后台用户
    public static function tempAdmin(string $identifier)
    {
        try {
            DB::beginTransaction();
            $data = [
                'identifier' => $identifier ,
                'username'   => 'waiter_' . random(6 , 'mixed' , true) ,
                'unique_code' => MiscUtil::uniqueCode() ,
                'is_temp'    => 1 ,
                'role'      => 'admin' ,
            ];
            $id = self::insertGetId($data);
            UserInfoModel::insert([
                'user_id'  => $id ,
                'nickname' => 'waiter_' . random(6 , 'mixed' , true)
            ]);
            DB::commit();
            return self::findById($id);
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
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
            ['is_temp' , '<=' , 1] ,
        ])->get();
        self::multiple($res);
        return $res;
    }

    public static function getIdByIdentifierAndRole(string $identifier , string $role = '')
    {
        $where = [
            ['identifier' , '=' , $identifier] ,
        ];
        if (!empty($role)) {
            $where[] = ['role' , '=' , $role];
        }
        $res = self::where($where)
            ->get();
        $id_list = [];
        foreach ($res as $v)
        {
            $id_list[] = $v->id;
        }
        return $id_list;
    }

    // 查询用户
    public static function findByIdentifierAndUsernameAndPassword(string $identifier , string $username = '' , string $password = '')
    {
        $res = self::where([
                ['identifier' , '=' , $identifier] ,
                ['username' , '=' , $username] ,
                ['password' , '=' , $password] ,
            ])
            ->first();
        self::single($res);
        return $res;
    }

    // 查询数据
    public static function findByIdentifierAndAreaCodeAndPhone(string $identifier , string $area_code , string $phone)
    {
        $res = self::where([
            ['identifier' , '=' , $identifier] ,
            ['area_code' , '=' , $area_code] ,
            ['phone' , '=' , $phone] ,
        ])
            ->first();
        self::single($res);
        return $res;
    }

    public static function findByIdentifierAndInviteCode(string $identifier , string $invite_code)
    {
        $res = self::where([
            ['identifier' , '=' , $identifier] ,
            ['invite_code' , '=' , $invite_code] ,
        ])
            ->first();
        self::single($res);
        return $res;
    }

    public function userOption()
    {
        return $this->hasOne(UserOptionModel::class , 'user_id' , 'id');
    }

    public static function findById($id)
    {
        $res = self::with(['userOption'])
            ->find($id);
        if (empty($res)) {
            return ;
        }
        $res = convert_obj($res);
        self::single($res);
        UserOptionModel::single($res->user_option);
        return $res;
    }

    public static function findByIdentifierAndPhone(string $identifier , string $phone)
    {
        $res = self::with(['userOption'])
            ->where([
                ['identifier' , '=' , $identifier] ,
                ['phone' , '=' , $phone] ,
            ])
            ->first();
        if (empty($res)) {
            return ;
        }
        $res = convert_obj($res);
        self::single($res);
        UserOptionModel::single($res->user_option);
        return $res;
    }
}