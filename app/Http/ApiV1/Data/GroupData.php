<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/16
 * Time: 13:38
 */

namespace App\Http\ApiV1\Data;


use App\Http\ApiV1\Cache\GroupCache;
use App\Http\ApiV1\Model\GroupModel;

class GroupData extends Data
{
    public static function findByIdentifierAndId(string $identifier , int $id)
    {
        $res = GroupCache::findByIdentifierAndId($identifier , $id);
        if (empty($res)) {
            return ;
        }
        $res->user = UserData::findByIdentifierAndId($identifier , $res->user_id);
        return $res;
    }

    public static function updateByIdentifierAndIdAndData(string $identifier , int $group_id , array $data)
    {
        GroupModel::updateById($group_id , $data);
        GroupCache::delByIdentifierAndId($identifier , $group_id);
    }

    public static function delByIdentifierAndId(string $identifier , int $id)
    {
        GroupModel::delById($id);
        GroupCache::delByIdentifierAndId($identifier , $id);
    }
}