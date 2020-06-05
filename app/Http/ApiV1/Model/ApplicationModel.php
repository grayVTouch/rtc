<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/11
 * Time: 16:00
 */

namespace App\Http\ApiV1\Model;


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
            ->orderBy('create_time' , 'desc')
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

    public static function delByTypeAndRelationUserId(string $type , int $relation_user_id)
    {
        return self::where([
            ['type' , '=' , $type] ,
            ['relation_user_id' , '=' , $relation_user_id] ,
        ])->delete();
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

    // 检查请求是否存在
    public static function findByUserIdAndOpTypeAndRelationUserIdForPrivate(int $user_id , string $op_type , string $relation_user_id)
    {
        $res = self::where([
                ['user_id' , '=' , $user_id] ,
                ['type' , '=' , 'private'] ,
                ['op_type' , '=' , $op_type] ,
                ['relation_user_id' , '=' , $relation_user_id] ,
            ])
            ->first();
        self::single($res);
        return $res;
    }

    public static function findByUserIdAndOpTypeAndGroupIdAndRelationUserIdForGroup(int $user_id , string $op_type , int $group_id , string $relation_user_id)
    {
        $res = self::where([
            ['user_id' , '=' , $user_id] ,
            ['type' , '=' , 'group'] ,
            ['op_type' , '=' , $op_type] ,
            ['group_id' , '=' , $group_id] ,
            ['relation_user_id' , '=' , $relation_user_id] ,
        ])
            ->first();
        self::single($res);
        return $res;
    }

    public static function updateByTypeAndUserIdAndRelationUserId(string $type , int $user_id , int $relation_user_id , array $data = [])
    {
        return self::where([
            ['type' , '=' , $type] ,
            ['user_id' , '=' , $user_id] ,
            ['relation_user_id' , '=' , $relation_user_id] ,
        ])->update($data);
    }
}