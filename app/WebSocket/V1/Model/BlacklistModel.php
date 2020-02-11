<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/30
 * Time: 14:05
 */

namespace App\WebSocket\V1\Model;


use function core\convert_obj;

class BlacklistModel extends Model
{
    protected $table = 'blacklist';

    public static function u_insertGetId(string $identifier , int $user_id , int $block_user_id)
    {
        return self::insertGetId([
            'identifier'       => $identifier ,
            'user_id'       => $user_id ,
            'block_user_id' => $block_user_id
        ]);
    }

    public function blockUser()
    {
        return $this->belongsTo(UserModel::class , 'block_user_id' , 'id');
    }

    public static function countByUserId(int $user_id)
    {
        return (int) (self::where('user_id' , $user_id)
            ->count());
    }


    public static function listByUserId(int $user_id , int $offset = 0 , int $limit = 20)
    {
        $res = self::with('blockUser')
            ->where('user_id' , $user_id)
            ->offset($offset)
            ->limit($limit)
            ->get();
        $res = convert_obj($res);
        foreach ($res as $v)
        {
            self::single($v);
            UserModel::single($v->block_user);
        }
        return $res;
    }

    public static function getByUserId(int $user_id)
    {
        $res = self::with('blockUser')
            ->where('user_id' , $user_id)
            ->get();
        $res = convert_obj($res);
        foreach ($res as $v)
        {
            self::single($v);
            UserModel::single($v->block_user);
        }
        return $res;
    }

    public static function unblockUser(int $user_id , int $block_user_id)
    {
        return self::where([
                ['user_id' , '=' , $user_id] ,
                ['block_user_id' , '=' , $block_user_id] ,
            ])
            ->delete();
    }

    public static function blocked(int $user_id , int $block_user_id): bool
    {
        return (self::where([
                ['user_id' , '=' , $user_id] ,
                ['block_user_id' , '=' , $block_user_id] ,
            ])
            ->count()) > 0;
    }


    public static function delByUserId(int $user_id)
    {
        return self::where('user_id' , $user_id)->delete();
    }

    public static function delByBlockUserId(int $block_user_id)
    {
        return self::where('block_user_id' , $block_user_id)->delete();
    }

    public static function exist(int $user_id , int $block_user_id)
    {
        return (self::where([
                ['user_id' , '=' , $user_id] ,
                ['block_user_id' , '=' , $block_user_id] ,
            ])->count()) > 0;
    }
}