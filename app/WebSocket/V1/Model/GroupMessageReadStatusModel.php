<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/18
 * Time: 10:34
 */

namespace App\WebSocket\V1\Model;


use function core\convert_obj;
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
            ->leftJoin('group_message as gm' , 'gmrs.group_message_id' , '=' , 'gm.id')
            ->where([
                ['gmrs.group_id' , '=' , $group_id] ,
                ['gmrs.user_id' , '=' , $user_id] ,
            ])
            ->whereNotIn('gm.type' , ['voice'])
            ->update([
                'gmrs.is_read' => $is_read
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

    public static function u_insertGetId(string $identifier , int $user_id , int $group_id , int $group_message_id , int $is_read = 0)
    {
        return self::insertGetId([
            'identifier' => $identifier ,
            'user_id' => $user_id ,
            'group_id' => $group_id ,
            'group_message_id' => $group_message_id ,
            'is_read' => $is_read ,
        ]);
    }

    public static function initByGroupMessageId(int $group_message_id , int $group_id , int $user_id)
    {
        // 这条语句耗时仅 花费 5ms
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
        $count = self::whereNotExists(function($query){
                $query->select('id')
                    ->from('delete_message')
                    ->where('type' , 'group')
                    ->whereRaw('rtc_group_message_read_status.group_message_id = rtc_delete_message.message_id');
            })
            ->where([
                ['user_id' , '=' , $user_id] ,
                ['is_read' , '=' , $is_read] ,
            ])
            ->count();
        return (int) $count;
    }

    // 某个用户针对单个群聊会话的未读消息数量
    public static function unreadCountByUserIdAndGroupId(int $user_id , int $group_id)
    {
        $count = GroupMessageModel::whereNotExists(function($query) use($user_id , $group_id){
            $query->select('id')
                ->from('group_message_read_status')
                ->where([
                    ['user_id' , '=' , $user_id] ,
                    ['group_id' , '=' , $group_id] ,
                ])
                ->whereRaw('rtc_group_message.id = rtc_group_message_read_status.group_message_id');
        })
            ->whereNotExists(function($query) use($user_id , $group_id){
                $query->select('id')
                    ->from('delete_message_for_private')
                    ->where([
                        ['user_id' , '=' , $user_id] ,
                        ['group_id' , '=' , $group_id] ,
                    ])
                    ->whereRaw('rtc_group_message.id = rtc_delete_message_for_private.message_id');
            })
            ->where('group_id' , $group_id)
            ->count();
        return (int) $count;
    }

    public static function delByGroupMessageId(int $group_message_id)
    {
        return self::where('group_message_id' , $group_message_id)->delete();
    }

    public static function findByUserIdAndGroupMessageId(int $user_id , int $group_message_id)
    {
        $res = self::where([
            ['user_id' , '=' , $user_id] ,
            ['group_message_id' , '=' , $group_message_id] ,
        ])->first();
        $res = convert_obj($res);
        self::single($res);
        return $res;
    }

    // 获取用户未读消息（排除 阅后即焚消息 + 语音消息）
    public static function unreadByUserIdAndGroupIdExcludeVoice(int $user_id , string $group_id)
    {
        $res = GroupMessageModel::whereNotExists(function($query) use($user_id , $group_id){
            $query->select('id')
                ->from('group_message_read_status')
                ->whereRaw('rtc_group_message.id = rtc_group_message_read_status.group_message_id')
                ->where([
                    ['user_id' , '=' , $user_id] ,
                    ['group_id' , '=' , $group_id] ,
                ]);
        })
            ->where([
                ['group_id' , '=' , $group_id] ,
            ])
            ->whereNotIn('type' , ['voice'])
            ->get();
        $res = convert_obj($res);
        self::multiple($res);
        return $res;
    }

    public static function unreadByUserIdAndGroupId(int $user_id , string $group_id)
    {
        $res = GroupMessageModel::whereNotExists(function($query) use($user_id , $group_id){
            $query->select('id')
                ->from('group_message_read_status')
                ->whereRaw('rtc_group_message.id = rtc_group_message_read_status.group_message_id')
                ->where([
                    ['user_id' , '=' , $user_id] ,
                    ['group_id' , '=' , $group_id] ,
                ]);
        })
            ->where([
                ['group_id' , '=' , $group_id] ,
            ])
//            ->whereNotIn('type' , ['voice'])
            ->get();
        $res = convert_obj($res);
        self::multiple($res);
        return $res;
    }
}