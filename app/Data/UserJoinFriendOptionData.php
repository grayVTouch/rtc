<?php

namespace App\Data;

use App\Model\UserJoinFriendOptionModel;

/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/13
 * Time: 10:50
 */

class UserJoinFriendOptionData
{
    public static function getByIdentifierAndUserId(string $identifier , int $user_id)
    {
        $res = UserJoinFriendOptionModel::getByUserId($user_id);
        foreach ($res as $v)
        {
            $v->join_friend_method = JoinFriendMethodData::findByIdentifierAndId($identifier , $v->join_friend_method_id);
        }
        return $res;
    }
}