<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/29
 * Time: 10:22
 */

namespace App\Util;


use App\Model\GroupModel;

class GroupUtil extends Util
{
    // 群信息
    public static function group(int $group_id)
    {
        return GroupModel::findById($group_id);
    }
}