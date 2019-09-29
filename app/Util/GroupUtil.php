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
        $group->limit_member = GroupMemberModel::getByGroupId($group->id , 10);
    }
}