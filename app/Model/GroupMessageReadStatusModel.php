<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/18
 * Time: 10:34
 */

namespace App\Model;


use Illuminate\Support\Facades\DB;

class GroupMessageReadStatusModel extends Model
{
    protected $table = 'group_message_read_status';
    public $timestamps = false;

    // 消息是否读取
    public static function isRead($user_id , $group_message_id)
    {
        return self::where([
                    ['user_id' , '=' , $user_id] ,
                    ['group_message_id' , '=' , $group_message_id] ,
                ])
                ->value('is_read');
    }

    public static function delByGroupId(int $group_id)
    {
//        return DB::table('group_message_read_status as gmrs')
//            ->leftJoin('group_message as gm' , 'gmrs.group_message_id' , '=' , 'gm.id')
//            ->where('gm.group_id' , $group_id)
//            ->delete();
        return self::where('group_id' , $group_id)
            ->delete();
    }

    // 未读消息数量
    public static function countByUserIdAndGroupId(int $user_id , int $group_id , int $is_read)
    {
        return self::where([
                ['group_id' , '=' , $group_id] ,
                ['user_id' , '=' , $user_id] ,
                ['is_read' , '=' , $is_read] ,
            ])
            ->count();
    }

    public static function updateStatusByUserIdAndGroupIdExcludeVoice(int $user_id , int $group_id , int $is_read = 1)
    {
        return self::from('group_message_read_status as gmrs')
            ->leftJoin('group_message as gm' , 'gmrs.message_id' , '=' , 'gm.id')
            ->where([
                ['gmrs.group_id' , '=' , $group_id] ,
                ['gmrs.user_id' , '=' , $user_id] ,
            ])
            ->whereNotIn('gm.type' , ['voice'])
            ->update([
                'is_read' => $is_read
            ]);
    }

    // 设置单挑消息已读/未读
    public static function setIsReadByUserIdAndGroupMessageId(int $user_id , int $group_message_id , int $is_read = 0)
    {
        return self::where([
            ['user_id' , '=' , $user_id] ,
            ['group_message_id' , '=' , $group_message_id] ,
        ])->update([
            'is_read' => $is_read
        ]);
    }

    public static function u_insertGetId(int $user_id , int $group_id , int $group_message_id , int $is_read = 0)
    {
        return self::insertGetId([
            'user_id' => $user_id ,
            'group_id' => $group_id ,
            'group_message_id' => $group_message_id ,
            'is_read' => $is_read ,
        ]);
    }

    public static function initByGroupMessageId(int $group_message_id , int $group_id , int $user_id)
    {
        $user_ids = GroupMemberModel::getUserIdByGroupId($group_id);
        foreach ($user_ids as $v)
        {
            $is_read = $v == $user_id ? 1 : 0;
            GroupMessageReadStatusModel::u_insertGetId($v , $group_id , $group_message_id , $is_read);
        }
        return $user_ids;
    }

    // 消息已读|未读
    public static function initByGroupMessageIdUseReaded(int $group_message_id , int $group_id)
    {
        $user_ids = GroupMemberModel::getUserIdByGroupId($group_id);
        foreach ($user_ids as $v)
        {
            GroupMessageReadStatusModel::u_insertGetId($v , $group_id , $group_message_id , 1);
        }
        return $user_ids;
    }

    public static function countByUserIdAndIsRead(int $user_id , int $is_read)
    {
        return (int) (self::where([
            ['user_id' , '=' , $user_id] ,
            ['is_read' , '=' , $is_read] ,
        ])->count());
    }

    public static function delByGroupMessageId(int $group_message_id)
    {
        return self::where('group_message_id' , $group_message_id)->delete();
    }

}