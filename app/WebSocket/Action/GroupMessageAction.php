<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/20
 * Time: 10:52
 */

namespace App\WebSocket\Action;

use App\Model\DeleteMessageModel;
use App\Model\GroupModel;
use App\Model\GroupMemberModel;
use App\Model\GroupMessageModel;
use App\Model\GroupMessageReadStatusModel;
use App\Model\UserModel;
use App\Util\MiscUtil;
use App\WebSocket\Auth;
use function core\array_unit;
use Core\Lib\Validator;
use App\WebSocket\Util\MessageUtil;


use function core\convert_obj;
use function core\obj_to_array;
use Exception;
use Illuminate\Support\Facades\DB;
use function WebSocket\ws_config;


class GroupMessageAction extends Action
{

    public static function history(Auth $auth , array $param)
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
            return self::error('未找到群对应信息' , 404);
        }
        $limit_id = empty($param['limit_id']) ? 0 : $param['limit_id'];
        $limit = empty($param['limit']) ? 0 : $param['limit'];
        $res = GroupMessageModel::history($auth->user->id , $group->id , $limit_id , $limit);
        foreach ($res as $v)
        {
            MessageUtil::handleGroupMessage($v);
        }
        return self::success($res);
    }

    // 设置未读消息数量
    public static function resetUnread(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'group_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        GroupMessageReadStatusModel::updateStatusByUserIdAndGroupId($auth->user->id , $param['group_id'] , 1);
        // 通知用户刷新会话列表
        $auth->push($auth->user->id , 'refresh_session');
        $auth->push($auth->user->id , 'refresh_unread_count');
        return self::success();
    }

    // 删除消息记录
    public static function delete(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'group_message_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $group_message_id = json_decode($param['group_message_id'] , true);
        if (empty($message_id)) {
            return self::error('请提供待删除的消息');
        }
        try {
            DB::beginTransaction();
            foreach ($group_message_id as $v)
            {
                $data = [
                    'user_id'   => $auth->user->id ,
                    'type'      => 'group' ,
                    'message_id' => $v
                ];
                DeleteMessageModel::insert($data);
            }
            DB::commit();
            $auth->push($auth->user->id , 'refresh_session');
            return self::success();
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

}