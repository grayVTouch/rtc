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

    public static function history(array $filter = [] , array $order = [] , int $limit = 20)
    {
        $filter['limit_id'] = $filter['limit_id'] ?? '';
        $order['field'] = $order['field'] ?? 'id';
        $order['value'] = $order['field'] ?? 'desc';
        $where = [];
        if ($filter['limit_id'] != '') {
            $where[] = ['id' , '<' , $filter['limit_id']];
        }
        $res = self::with(['user'])
            ->where($where)
            ->orderBy($order['field'] , $order['value'])
            ->limit($limit);
        $res = convert_obj($res);
        foreach ($res as $v)
        {
            self::single($v);
            UserModel::single($v->user);
        }
        return $res;
    }

    // 最新一条数据
    public static function recentMessage(string $chat_id)
    {
        $res = self::with(['user'])
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

}