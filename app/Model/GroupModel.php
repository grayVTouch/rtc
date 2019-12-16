<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/12
 * Time: 10:02
 */

namespace App\Model;


use function core\convert_obj;
use function core\random;
use Illuminate\Support\Facades\DB;

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
            ['is_temp' , '=' , 1] ,
        ])->get();
        self::multiple($res);
        return $res;
    }

    public static function serviceGroup()
    {
        $res = self::with(['user'])
            ->where('is_service' , 1)
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

    // 时效群，并且已经过期
    public static function expiredGroup()
    {
        $datetime = date('Y-m-d H:i:s' , time());
        $res = self::with(['user'])
            ->where([
                ['type' , '=' , 2] ,
                ['expire' , '<' , $datetime] ,
            ])
            ->get();
        $res = convert_obj($res);
        foreach ($res as $v)
        {
            self::single($v);
            UserModel::single($v->user);
        }
        return $res;
    }

    public function user()
    {
        return $this->belongsTo(UserModel::class , 'user_id' , 'id');
    }

    public static function searchByUserIdWithName(int $user_id , $value)
    {
        $res = self::from('group_member as gm')
            ->leftJoin('group as g' , 'g.id' , '=' , 'gm.group_id')
            ->where([
                ['g.name' , 'like' , "%{$value}%"] ,
                ['gm.user_id' , '=' , $user_id] ,
            ])
            ->select('g.*', 'gm.create_time as join_time')
            ->get();
        $res = convert_obj($res);
        self::multiple($res);
        return $res;
    }

    public static function findById(int $id)
    {
        $res = self::find($id);
        $res = convert_obj($res);
        return $res;
    }

}