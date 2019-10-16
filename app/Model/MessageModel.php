<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/16
 * Time: 16:53
 */

namespace App\Model;


use function core\convert_obj;
use Illuminate\Support\Facades\DB;

class MessageModel extends Model
{
    protected $table = 'message';
    public $timestamps = false;

    /**
     * 新增数据
     *
     * @param int $user_id
     * @param int $group_id
     * @param string $type
     * @param string $message
     * @param string $extra
     * @param string $flag
     * @return mixed
     */
    public static function u_insertGetId(int $user_id , string $chat_id , string $type , string $message = '' , string $extra = '' , $flag = 'normal')
    {
        return self::insertGetId([
            'user_id' => $user_id ,
            'chat_id' => $chat_id ,
            'type'  => $type ,
            'message' => $message ,
            'extra' => $extra ,
            'flag' => $flag ,
        ]);
    }

    /**
     * 删除消息
     *
     * @param string $chat_id
     * @return mixed
     */
    public static function delByChatId(string $chat_id)
    {
        return self::where('chat_id' , $chat_id)->delete();
    }

    public function user()
    {
        return $this->belongsTo(UserModel::class , 'user_id' , 'id');
    }

    // 查询消息
    public static function findById(int $id)
    {
        $res = self::with(['user'])
            ->find($id);
        if (empty($res)) {
            return ;
        }
        $res = convert_obj($res);
        self::single($res);
        UserModel::single($res->user);
        return $res;
    }

    public static function history(int $user_id , string $chat_id , int $limit_id = 0 , int $limit = 20)
    {
        $where = [
            ['chat_id' , '=' , $chat_id] ,
        ];
        if ($limit_id != '') {
            $where[] = ['id' , '<' , $limit_id];
        }
        $res = self::with(['user'])
            ->from('message as m')
            ->whereNotExists(function($query) use($user_id){
                $query->select('dm.id')
                    ->from('delete_message as dm')
                    ->whereRaw('rtc_m.id = rtc_dm.message_id')
                    ->where([
                        ['dm.type' , '=' , 'private'] ,
                        ['dm.user_id' , '=' , $user_id] ,
                    ]);
            })
            ->where($where)
            ->orderBy('id' , 'desc')
            ->limit($limit)
            ->get();
        $res = convert_obj($res);
        foreach ($res as $v)
        {
            self::single($v);
            UserModel::single($v->user);
        }
        return $res;
    }

    // 最新一条数据
    public static function recentMessage(int $user_id , string $chat_id)
    {
        $res = self::with(['user'])
            ->from('message as m')
            ->whereNotExists(function($query) use($user_id){
                $query->select('dm.id')
                    ->from('delete_message as dm')
                    ->whereRaw('rtc_dm.message_id = rtc_m.id')
                    ->where([
                        ['dm.user_id' , '=' , $user_id] ,
                        ['dm.type' , '=' , 'private'] ,
                    ]);
            })
            ->where('chat_id' , $chat_id)
            ->orderBy('id' , 'desc')
            ->first();
        if (empty($res)) {
            return ;
        }
        self::single($res);
        UserModel::single($res->user);
        return $res;
    }

    // 私聊会话未读消息
    public static function countByChatIdAndUserIdAndIsRead(string $chat_id , int $user_id , int $is_read)
    {
        return (int) (DB::table('message as m')
            ->leftJoin('message_read_status as mrs' , 'm.id' , '=' , 'mrs.message_id')
            ->where([
                ['m.chat_id' , '=' , $chat_id] ,
                ['mrs.user_id' , '=' , $user_id] ,
                ['mrs.is_read' , '=' , $is_read] ,
            ])
            ->count());
    }

    // 删除调已经被读取的阅后即焚消息
    public static function delFriendReadedByChatId(string $chat_id)
    {
        // 特别注意，这个地方表明不能用别名
        // 因为如果用别名 + exists 语句的话
        // 需要再 delete 后面跟上要删除的表明
        // 很显然，laravel 的查询构造器不支持
        // 所以，请使用完整的别名即可
        return self::from('message')
            ->where('m.chat_id' , $chat_id)
            ->whereExists(function($query){
                // 对方已读
                $query->select('message_read_status.id')
                    ->from('message_read_status')
                    ->whereRaw('rtc_message_read_status.message_id = rtc_message.id')
                    ->whereRaw('rtc_message_read_status.user_id != rtc_message.user_id')
                    ->where('message_read_status.is_read' , 1);
            })
            ->delete();
    }

    // 获取所有阅后即焚消息（好友已读）
    public static function getIdsWithFriendReadedByChatId(string $chat_id)
    {
        return self::from('message as m')
            ->where('m.chat_id' , $chat_id)
            ->whereExists(function($query){
                // 对方已读
                $query->select('mrs.id')
                    ->from('message_read_status as mrs')
                    ->whereRaw('rtc_mrs.message_id = rtc_m.id')
                    ->whereRaw('rtc_mrs.user_id != rtc_m.user_id')
                    ->where('mrs.is_read' , 1);
            })
            ->column('m.id');
    }

    // 获取所有的聊天记录
    public static function getByChatId(string $chat_id)
    {
        $res = self::where('chat_id' , $chat_id)
            ->get();
        $res = convert_obj($res);
        self::multiple($res);
        return $res;
    }

    public static function countByChatIdAndValue(string $chat_id , $value)
    {
        return self::where([
                ['chat_id' , '=' , $chat_id] ,
                ['message' , 'like' , "%{$value}%"] ,
            ])
            ->count();
    }

    public static function searchByChatIdAndValueAndAndLimitIdAndLimit(string $chat_id , $value , int $limit_id = 0 , int $limit = 20)
    {
        $where = [
            ['chat_id' , '=' , $chat_id] ,
            ['message' , 'like' , "%{$value}%"] ,
        ];
        if (!empty($limit_id)) {
            $where[] = ['id' , '<' , $limit_id];
        }
        $res = self::with(['user'])
            ->where($where)
            ->limit($limit)
            ->orderBy('id' , 'desc')
            ->get();
        $res = convert_obj($res);
        foreach ($res as $v)
        {
            self::single($v);
            UserModel::single($v->user);
        }
        return $res;
    }
}