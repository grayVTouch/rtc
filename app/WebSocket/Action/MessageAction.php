<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/19
 * Time: 21:34
 */

namespace App\WebSocket\Action;

use App\Model\Group;
use App\Model\GroupMember;
use App\Model\GroupMessage;
use App\Model\GroupMessageReadStatus;
use App\Model\User;
use Core\Lib\Validator;
use function core\obj_to_array;

class MessageAction extends Action
{

    public static function groupHistory(array $param)
    {
        // 获取群聊数据
        $validator = Validator::make($param , [
            'group_id' => 'required' ,
            'group_message_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $group = Group::findById($param['group_id']);
        if (empty($group)) {
            return self::error('未找到 group_id = ' . $param['group_id'] . ' 对应群信息' , 404);
        }
        $res = GroupMessage::history($group->id , $param['group_message_id'] , config('app.limit'));
        return self::success($res);
    }

    public static function groupRecent(array $param)
    {
        // 获取群聊数据
        $validator = Validator::make($param , [
            'group_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $group = Group::findById($param['group_id']);
        if (empty($group)) {
            return self::error('未找到 group_id = ' . $param['group_id'] . ' 对应群信息' , 404);
        }
        $res = GroupMessage::history($group->id , 0 , config('app.limit'));
        return self::success($res);
    }

    public static function session(User $user)
    {
        // 群聊
        $group = GroupMember::getGroupByUserId($user->id);
        foreach ($group as $v)
        {
            $recent_message = GroupMessage::recentMessage($v->id);
            if (empty($recent_message)) {
                continue ;
            }
            $v->recent_message = $recent_message;
            $v->unread = GroupMessageReadStatus::unreadCountByUserIdAndGroupId($user->id , $v->id);
        }
        $group = obj_to_array($group);

        // todo 私聊
        $session = array_merge($group);
        usort($session , function($a , $b){
            if ($a['recent_message']['create_time'] == $b['recent_message']['create_time']) {
                return 0;
            }
            return $a['recent_message']['create_time'] > $b['recent_message']['create_time'] ? -1 : 1;
        });
        return self::success($session);
    }
}