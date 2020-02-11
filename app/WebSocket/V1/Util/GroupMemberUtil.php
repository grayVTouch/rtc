<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/16
 * Time: 14:10
 */

namespace App\WebSocket\V1\Util;


use App\WebSocket\V1\Data\GroupData;
use App\WebSocket\V1\Data\UserData;

class GroupMemberUtil extends Util
{
    public static function handle($member)
    {
        if (empty($member)) {
            return ;
        }
        $member->user = UserData::findByIdentifierAndId($member->identifier , $member->user_id);
        $member->group = GroupData::findByIdentifierAndId($member->identifier , $member->group_id);
        return $member;
    }
}