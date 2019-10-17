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
use App\Util\UserUtil;
use App\WebSocket\Auth;
use Core\Lib\Validator;

class SearchAction extends Action
{

    // 全网搜索
    public static function searchInNet(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'keyword' => 'required'
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $res = [];
        // 搜索好友
        if ($user_use_id = UserModel::findById($param['keyword'])) {
            UserUtil::handle($user_use_id);
            $res[] = $user_use_id;
        }
        if ($user_use_nickname = UserModel::findByIdentifierAndNickname($auth->identifier , $param['keyword'])) {
            UserUtil::handle($user_use_nickname);
            $res[] = $user_use_nickname;
        }
        if ($user_use_phone = UserModel::findByIdentifierAndPhone($auth->identifier , $param['keyword'])) {
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
        $qualified_friends = [];
        if (mb_strpos($auth->user->nickname , $param['value']) !== false) {
            $qualified_friends[] = $auth->user;
        }

        // 搜索好友
        $friends = FriendModel::searchByUserIdAndValueAndLimitIdAndLimit($auth->user->id, $param['value'], 0 , empty($qualified_friends) ? $limit : $limit - 1);
        foreach ($friends as $v)
        {
            UserUtil::handle($v->friend , $auth->user->id);
            $qualified_friends[] = $v->friend;
        }

        // 搜索群组（搜索群名称）
        $qualified_group = GroupMemberModel::searchByUserIdAndValueAndLimitIdAndLimit($auth->user->id, $param['value'], 0 , $limit);
        $group_ids = GroupMemberModel::getGroupIdByUserId($auth->user->id);
        foreach ($group_ids as $v)
        {
            GroupUtil::handle($v->group);
            UserUtil::handle($v->user);
            UserUtil::handle($v->member , $auth->user->id);
        }

        // 好友列表
        $friend_ids = FriendModel::getFriendIdByUserId($auth->user->id);
        $qualified_friend_for_history = [];
        foreach ($friend_ids as $v)
        {
            $chat_id = ChatUtil::chatId($auth->user->id , $v);
            $relation_message_count= MessageModel::countByChatIdAndValue($chat_id , $param['value']);
            $friend = FriendModel::findByUserIdAndFriendId($auth->user->id , $v);
            if ($relation_message_count < 1) {
                // 没有相关聊天记录，跳过
                continue ;
            }
            $friend->relation_message_count = $relation_message_count;
            // 私聊
            $friend->type = 'private';
            UserUtil::handle($friend->user);
            UserUtil::handle($friend->friend , $auth->user->id);
            $qualified_friend_for_history[] = $friend;
        }

        // 群列表
        $qualified_group_for_history = [];
        foreach ($group_ids as $v)
        {
            $relation_message_count = GroupMessageModel::countByGroupIdAndValue($v , $param['value']);
            if ($relation_message_count < 1) {
                // 没有相关聊天记录
                continue ;
            }
            $group = GroupModel::findById($v);
            $group->relation_message_count = $relation_message_count;
            // 群聊
            $group->type = 'group';
            GroupUtil::handle($group);
            UserUtil::handle($group->user);
            $qualified_group_for_history[] = $group;
        }
        // 合并消息列表
        $history = array_merge($qualified_friend_for_history , $qualified_group_for_history);
        // 合并记录排序
        usort($history , function($a , $b){
            if ($a['create_time'] == $b['create_time']) {
                return 0;
            }
            return $a['create_time'] > $b['create_time'] ? -1 : 1;
        });
        $res = [
            // 符合提交的好友
            'friend' => $qualified_friends ,
            // 符合条件的群组
//            'group' => $qualified_groups ,
            // 符合条件的聊天记录
            'history' => $history ,
        ];
        return self::success($res);
    }
}