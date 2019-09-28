<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/20
 * Time: 10:52
 */

namespace App\WebSocket\Action;

use App\Model\GroupModel;
use App\Model\GroupMemberModel;
use App\Model\GroupMessageModel;
use App\Model\GroupMessageReadStatusModel;
use App\Model\UserModel;
use App\Util\MiscUtil;
use App\WebSocket\Auth;
use Core\Lib\Validator;
use App\WebSocket\Util\MessageUtil;


use function core\convert_obj;
use function core\obj_to_array;


class GroupMessageAction extends Action
{

    public static function groupHistory(Auth $auth , array $param)
    {
        // 获取群聊数据
        $validator = Validator::make($param , [
            'group_id' => 'required' ,
            'group_message_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $group = GroupModel::findById($param['group_id']);
        if (empty($group)) {
            return self::error('未找到 group_id = ' . $param['group_id'] . ' 对应群信息' , 404);
        }
        $res = GroupMessageModel::history($group->id , $param['group_message_id'] , config('app.limit'));
        $res = convert_obj($res);
        foreach ($res as $v)
        {
            MessageUtil::handleGroupMessage($v);
        }
        $res = obj_to_array($res);
        usort($res , function($a , $b){
            if ($a['id'] == $b['id']) {
                return 0;
            }
            return $a['id'] > $b['id'] ? 1 : -1;
        });
        return self::success($res);
    }

    public static function groupRecent(Auth $auth , array $param)
    {
        // 获取群聊数据
        $validator = Validator::make($param , [
            'group_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $group = GroupModel::findById($param['group_id']);
        if (empty($group)) {
            return self::error('未找到 group_id = ' . $param['group_id'] . ' 对应群信息' , 404);
        }
        $res = GroupMessageModel::history($group->id , 0 , config('app.limit'));
        // 消息模型关联
        $res = convert_obj($res);
        foreach ($res as $v)
        {
            MessageUtil::handleGroupMessage($v);
        }
        $res = obj_to_array($res);
        usort($res , function($a , $b){
            if ($a['id'] == $b['id']) {
                return 0;
            }
            return $a['id'] > $b['id'] ? 1 : -1;
        });
        return self::success($res);
    }

    // 设置未读消息数量
    public static function resetGroupUnread(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'group_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $res = GroupMessageReadStatusModel::updateStatus($auth->user->id , $param['group_id'] , 1);
        // 通知用户刷新会话列表
        $auth->push($auth->user->id , 'refresh_session');
        return self::success($res);
    }
}