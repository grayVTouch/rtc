<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/10/12
 * Time: 13:57
 */

namespace App\WebSocket\Action;


use App\Model\FriendModel;
use App\Model\GroupMemberModel;
use App\Model\GroupMessageModel;
use App\Model\GroupModel;
use App\Model\MessageModel;
use App\Model\UserModel;
use App\Util\ChatUtil;
use App\Util\GroupUtil;
use App\Util\SearchUtil;
use App\Util\SessionUtil;
use App\Util\UserUtil;
use App\WebSocket\Auth;
use Core\Lib\Validator;

class SearchAction extends Action
{

    // 全网搜索
    public static function searchInNet(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'value' => 'required'
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $res = [];
        // 搜索好友
        if ($user_use_id = UserModel::findById($param['value'])) {
            UserUtil::handle($user_use_id);
            $res[] = $user_use_id;
        }
        if ($user_use_nickname = UserModel::findByIdentifierAndNickname($auth->identifier , $param['value'])) {
            UserUtil::handle($user_use_nickname);
            $res[] = $user_use_nickname;
        }
        if ($user_use_phone = UserModel::findByIdentifierAndPhone($auth->identifier , $param['value'])) {
            UserUtil::handle($user_use_phone);
            $res[] = $user_use_phone;
        }
        return self::success($res);
    }

    // 本地搜索
    // 第一：好友搜索
    // 第二：群搜索
    // 第三：历史消息记录
    public static function searchInLocal(Auth $auth , array $param)
    {
        $validator = Validator::make($param, [
            'value' => 'required',
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $limit = 3;

        // 搜索自身
        $qualified_users = SearchUtil::searchUserByUserIdAndValueAndLimitForLocal($auth->user->id , $param['value'] , $limit);
        // 搜索群组（搜索群名称）
        $qualified_groups = SearchUtil::searchGroupByUserIdAndValueAndLimitForLocal($auth->user->id , $param['value'] , $limit);
        // 私聊记录
        $qualified_private_for_history = SearchUtil::searchPrivateSessionByUserIdAndValueAndLimitForLocal($auth->user->id , $param['value'] , $limit);
        // 群聊记录
        $qualified_group_for_history = SearchUtil::searchGroupSessionByUserIdAndValueAndLimitForLocal($auth->user->id , $param['value'] , $limit);
        // 合并消息列表
        $history = array_merge($qualified_private_for_history , $qualified_group_for_history);
        // 合并记录排序
        usort($history , function($a , $b){
            if ($a->create_time == $b->create_time) {
                return 0;
            }
            return $a->create_time > $b->create_time ? -1 : 1;
        });
        $res = [
            // 符合提交的好友
            'user' => $qualified_users ,
            // 符合条件的群组
            'group' => $qualified_groups ,
            // 符合条件的聊天记录
            'history' => $history ,
        ];
        return self::success($res);
    }

    // 本地搜索-完整的好友列表
    public static function searchForFriendInLocal(Auth $auth , array $param)
    {
        $res = SearchUtil::searchUserByUserIdAndValueAndLimitForLocal($auth->user->id , $param['value']);
        return self::success($res);
    }

    // 本地搜索-完整的群列表
    public static function searchForGroupInLocal(Auth $auth , array $param)
    {
        $res = SearchUtil::searchGroupByUserIdAndValueAndLimitForLocal($auth->user->id , $param['value']);
        return self::success($res);
    }

    // 本地搜索-私聊会话记录
    public static function searchForSessionInLocal(Auth $auth , array $param)
    {
        // 私聊记录
        $qualified_private_for_history = SearchUtil::searchPrivateSessionByUserIdAndValueAndLimitForLocal($auth->user->id , $param['value']);
        // 群聊记录
        $qualified_group_for_history = SearchUtil::searchGroupSessionByUserIdAndValueAndLimitForLocal($auth->user->id , $param['value']);
        // 合并消息列表
        $history = array_merge($qualified_private_for_history , $qualified_group_for_history);
        // 合并记录排序
        usort($history , function($a , $b){
            if ($a->create_time == $b->create_time) {
                return 0;
            }
            return $a->create_time > $b->create_time ? -1 : 1;
        });
        return self::success($history);
    }

    // 本地搜索-单个私聊会话的聊天记录
    public static function searchForPrivateHistoryInLocal(Auth $auth , array $param)
    {

        $res = SearchUtil::searchPrivateHistoryByUserIdChatIdAndValueAndLimitIdAndLimitForLocal();
    }
}