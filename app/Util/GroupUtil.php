<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/29
 * Time: 10:22
 */

namespace App\Util;


use App\Model\GroupMemberModel;
use App\Model\GroupModel;

class GroupUtil extends Util
{
    /**
     * @param \App\Model\GroupModel|\StdClass $group
     * @return null
     */
    public static function handle($group)
    {
        $member = GroupMemberModel::getByGroupId($group->id , 9);
        $member_avatar = [];
        foreach ($member as $v)
        {
            $member_avatar[] = empty($v->user) ? $v->user->avatar : '';
        }
        $group->member_avatar = $member_avatar;
    }
}