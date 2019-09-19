<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/18
 * Time: 10:34
 */

namespace App\Model;


use Illuminate\Support\Facades\DB;

class GroupMessageReadStatus extends Model
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
        return DB::table('group_message_read_status as gmrs')
            ->leftJoin('group_message as gm' , 'gmrs.group_message_id' , '=' , 'gm.id')
            ->where('gm.group_id' , $group_id)
            ->delete();
    }

    public static function unreadCountByUserIdAndGroupId(int $user_id , int $group_id)
    {
        return DB::table('group_message as gm')
            ->leftJoin('group_message_read_status as gmrs' , 'gm.id' , '=' , 'gmrs.group_message_id')
            ->where([
                ['gm.group_id' , '=' , $group_id] ,
                ['gmrs.user_id' , '=' , $user_id] ,
                ['gmrs.is_read' , '=' , 0] ,
            ])
            ->count();
    }

    public static function updateStatus(int $user_id , int $group_id , int $is_read = 1)
    {
        return DB::table('group_message_read_status as gmrs')
            ->leftJoin('group_message as gm' , 'gmrs.group_message_id' , '=' , 'gm.id')
            ->where([
                ['gmrs.user_id' , '=' , $user_id] ,
                ['gm.group_id' , '=' , $group_id] ,
            ])->update([
                'gmrs.is_read' => $is_read
            ]);
    }

    public static function u_insertGetId(int $user_id , int $group_message_id , int $is_read = 0)
    {
        return self::insertGetId([
            'user_id' => $user_id ,
            'group_message_id' => $group_message_id ,
            'is_read' => $is_read ,
        ]);
    }

    public static function initByGroupMessageId(int $group_message_id , int $group_id , int $user_id)
    {
        $user_ids = GroupMember::getUserIdByGroupId($group_id);
        foreach ($user_ids as $v)
        {
            $is_read = $v == $user_id ? 1 : 0;
            GroupMessageReadStatus::u_insertGetId($v , $group_message_id , $is_read);
        }
        return $user_ids;
    }
}