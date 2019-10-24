<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/11
 * Time: 16:00
 */

namespace App\Model;


use function core\convert_obj;
use Exception;

class ApplicationModel extends Model
{
    protected $table = 'application';

    public function user()
    {
        return $this->belongsTo(UserModel::class , 'user_id' , 'id');
    }

    public static function listByUserId(int $user_id , int $offset = 0 , int $limit = 20)
    {
        $where = [
            ['user_id' , '=' , $user_id] ,
        ];
        $res = self::with(['user'])
            ->where($where)
            ->orderBy('id' , 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();
        $res = convert_obj($res);
        foreach ($res as $v)
        {
            self::single($v);
        }
        return $res;
    }

    public static function countByUserId(int $user_id)
    {
        return self::where('user_id' , $user_id)
            ->count();
    }

    /**
     * @param int $user_id
     * @param int $is_read
     * @return int
     * @throws Exception
     */
    public static function countByUserIdAndIsRead(int $user_id , int $is_read)
    {
        $range = [0,1];
        if (!in_array($is_read , $range)) {
            throw new Exception('不支持的类型，当前受支持的类型有：' . implode(',' , $range));
        }
        if ($is_read) {
            // 申请状态：approve-同意；refuse-拒绝；wait-等待处理',
            return (int) (self::where('user_id' , $user_id)
                ->whereIn('status' , ['approve' , 'refuse'])
                ->count());
        }
        return (int) (self::where('user_id' , $user_id)
            ->whereIn('status' , ['wait'])
            ->count());
    }

    public static function delByUserId(int $user_id)
    {
        return self::where('user_id' , $user_id)->delete();
    }

    public static function delGroupApplication(int $group_id)
    {
        return self::where([
            ['type' , '=' , 'group'] ,
            ['group_id' , '=' , $group_id]
        ])->delete();
    }

    public static function hasOther(int $user_id , array $id_list = [])
    {
        return (self::where([
                        ['user_id' , '!=' , $user_id] ,
                    ])
                    ->whereIn('id' , $id_list)
                    ->count()) > 0;
    }
}