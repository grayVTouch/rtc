<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/18
 * Time: 10:34
 */

namespace App\Model;

use function core\convert_obj;
use Illuminate\Support\Facades\DB;

class MessageReadStatusModel extends Model
{
    protected $table = 'message_read_status';
    public $timestamps = false;

    public static function u_insertGetId(string $identifier , int $user_id , string $chat_id , int $message_id , int $is_read)
    {
        return self::insertGetId([
            'identifier' => $identifier ,
            'user_id' => $user_id ,
            'chat_id' => $chat_id ,
            'message_id' => $message_id ,
            'is_read' => $is_read
        ]);
    }

    /**
     * 更新消息读取状态
     * @param int $is_read 0 | 1
     */
    public static function updateReadStatusByUserIdAndMessageId(int $user_id , int $message_id , int $is_read)
    {
        return self::where([
            ['user_id' , '=' , $user_id] ,
            ['message_id' , '=' , $message_id] ,
        ])->update([
            'is_read' => $is_read
        ]);
    }

    /**
     * 更新消息读取状态
     *
     */
    public static function updateReadStatusByUserIdAndMessageIdExcludeBurn(int $user_id , int $message_id , int $is_read)
    {
        return self::where([
            ['user_id' , '=' , $user_id] ,
            // 非阅后即焚
            ['flag' , '<>' , 'burn'] ,
            ['message_id' , '=' , $message_id] ,
        ])->update([
            'is_read' => $is_read
        ]);
    }

    /**
     * 更新消息读取状态
     *
     */
    public static function updateReadStatusByUserIdAndMessageIds(int $user_id , array $message_ids , int $is_read)
    {
        return self::where([
            ['user_id' , '=' , $user_id]
        ])
            ->whereIn('message_id' , $message_ids)
            ->update([
                'is_read' => $is_read
            ]);
    }


    /**
     * 更新消息读取状态
     *
     */
    public static function updateReadStatusByUserIdAndMessageIdsExcludeBurn(int $user_id , array $message_ids , int $is_read)
    {
        return self::from('message_read_status as mrs')
            ->leftJoin('message as m' , 'mrs.message_id' , '=' , 'm.id')
            ->where([
                ['mrs.user_id' , '=' , $user_id] ,
                // 非阅后即焚
                ['m.flag' , '<>' , 'burn'] ,
            ])
            ->whereIn('mrs.message_id' , $message_ids)
            ->update([
                'mrs.is_read' => $is_read
            ]);
    }

    /**
     * 更新消息读取状态
     *
     */
    public static function updateReadStatusByUserIdAndChatId(int $user_id , string $chat_id , int $is_read)
    {
        return self::where([
            ['user_id' , '=' , $user_id] ,
            ['chat_id' , '=' , $chat_id] ,
        ])
            ->update([
                'is_read' => $is_read
            ]);
    }

    /**
     * 更新消息读取状态
     *
     */
    public static function updateReadStatusByUserIdAndChatIdExcludeBurnAndVoice(int $user_id , string $chat_id , int $is_read)
    {
        self::from('message_read_status as mrs')
            ->leftJoin('message as m' , 'mrs.message_id' , '=' , 'm.id')
            ->where([
                ['mrs.user_id' , '=' , $user_id] ,
                ['mrs.chat_id' , '=' , $chat_id] ,
                // 非阅后即焚
                ['m.flag' , '<>' , 'burn'] ,
            ])
            ->whereNotIn('m.type' , ['voice'])
            ->update([
                'mrs.is_read' => $is_read
            ]);
//        $res = DB::getQueryLog();
        return 0;
    }

    // 某个私聊会话针对某个用户的未读消息数量
    public static function unreadCountByUserIdAndChatId(int $user_id , string $chat_id)
    {
        $count = self::from('message_read_status')
            ->whereNotExists(function($query){
                $query->select('id')
                    ->from('message_read_status')
                    ->where([
                        ['user_id' , '=' , $user_id] ,
                        ['chat_id' , '=' , $chat_id] ,
                    ])
                    ->whereRaw('rtc_message.id = rtc_message_read_status.message_id');
            })
            ->whereNotExists(function($query) use($user_id , $chat_id){
                $query->select('id')
                    ->from('delete_message_for_private')
                    ->where([
                        ['user_id' , '=' , $user_id] ,
                        ['chat_id' , '=' , $chat_id] ,
                    ])
                    ->whereRaw('rtc_message.id = rtc_delete_message_for_private.message_id');
            })
            ->where('chat_id' , $chat_id)
            ->count();
        return (int) $count;
    }

    public static function countByUserIdAndChatIdAndIsRead(int $user_id , string $chat_id , int $is_read)
    {
        $count = self::from('message_read_status')
            ->whereNotExists(function($query){
                $query->select('id')
                    ->from('delete_message')
                    ->where('type' , 'private')
                    ->whereRaw('rtc_message_read_status.message_id = rtc_delete_message.message_id');
            })
            ->where([
                ['user_id' , '=' , $user_id] ,
                ['chat_id' , '=' , $chat_id] ,
                ['is_read' , '=' , $is_read] ,
            ])
            ->count();
        return (int) $count;
    }

    // 删除读取状态
    public static function delByMessageIds(array $message_ids)
    {
        return self::whereIn('message_id' , $message_ids)
            ->delete();
    }

    // 删除读取状态-单条
    public static function delByMessageId(int $message_id)
    {
        return self::delByMessageIds([$message_id]);
    }

    public static function delByChatId(string $chat_id)
    {
        return self::where('chat_id' , $chat_id)
            ->delete();
    }

    public static function findByUserIdAndMessageId(int $user_id , int $message_id)
    {
        $res = self::where([
                ['user_id' , '=' , $user_id] ,
                ['message_id' , '=' , $message_id] ,
            ])
            ->first();
        $res = convert_obj($res);
        self::single($res);
        return $res;
    }

    public static function delByUserIdAndMessageId(int $user_id , int $message_id)
    {
        return self::where([
            ['user_id' , '=' , $user_id] ,
            ['message_id' , '=' , $message_id] ,
        ])->delete();
    }

    // 获取用户未读消息（排除 阅后即焚消息 + 语音消息）
    public static function unreadByUserIdAndChatIdExcludeBurnAndVoice(int $user_id , string $chat_id)
    {
        $res = MessageModel::whereNotExists(function($query) use($user_id , $chat_id){
                $query->select('id')
                    ->from('message_read_status')
                    ->whereRaw('rtc_message.id = rtc_message_read_status.message_id')
                    ->where([
                        ['user_id' , '=' , $user_id] ,
                        ['chat_id' , '=' , $chat_id] ,
                    ]);
            })
            ->where([
                ['chat_id' , '=' , $chat_id] ,
                ['flag' , '=' , 'normal'] ,
            ])
            ->whereNotIn('type' , ['voice'])
            ->get();
        $res = convert_obj($res);
        self::multiple($res);
        return $res;
    }

    // 获取用户未读消息（排除 阅后即焚消息 + 语音消息）
    public static function unreadByUserIdAndChatId(int $user_id , string $chat_id)
    {
        $res = MessageModel::whereNotExists(function($query) use($user_id , $chat_id){
            $query->select('id')
                ->from('message_read_status')
                ->whereRaw('rtc_message.id = rtc_message_read_status.message_id')
                ->where([
                    ['user_id' , '=' , $user_id] ,
                    ['chat_id' , '=' , $chat_id] ,
                ]);
        })
            ->where([
                ['chat_id' , '=' , $chat_id] ,
//                ['flag' , '=' , 'normal'] ,
            ])
//            ->whereNotIn('type' , ['voice'])
            ->get();
        $res = convert_obj($res);
        self::multiple($res);
        return $res;
    }

}
