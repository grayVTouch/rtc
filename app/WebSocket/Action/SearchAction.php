<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/10/12
 * Time: 13:57
 */

namespace App\WebSocket\Action;


use App\Model\FriendModel;
use App\WebSocket\Auth;
use Core\Lib\Validator;

class SearchAction extends Action
{
    // 本地搜索
    public static function searchInLocal(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'value' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }

        $limit = 3;
        // 搜索好友（昵称 ）
        $friend_for_nickname = FriendModel::searchByUserIdAndNicknameAndLimit($auth->user->id , $param['value'] , $limit);

        // 搜索好友（别名）
        $friend_for_alias = FriendModel::searchByUserIdAndAliasAndLimit($auth->user->id , $param['value'] , $limit);

        // 搜索群组
//        $group =

        // 搜索聊天记录
    }
}