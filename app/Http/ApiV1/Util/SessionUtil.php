<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/10/17
 * Time: 10:26
 */

namespace App\Http\ApiV1\Util;


use App\Http\ApiV1\Model\GroupMemberModel;
use App\Http\ApiV1\Model\GroupModel;
use App\Http\ApiV1\Model\SessionModel;
use App\Http\ApiV1\Model\UserModel;
use function core\array_unit;

class SessionUtil extends Util
{
    // 创建 或 更新会话
    public static function createOrUpdate(string $identifier , int $user_id , string $type , $target_id = '')
    {
        // 检查 type 是否正确
        $type_range = config('business.session_type');
        if (!in_array($type , $type_range)) {
            return self::error('不支持的 type，当前受支持的 type 有' . implode(' , ' , $type_range));
        }
        switch ($type)
        {
            case 'private':
                $user_ids = ChatUtil::userIds($target_id);
                $users = UserModel::getByIds($user_ids);
                if (count($users) != 2) {
                    return self::error('创建会话失败！存在不存在的用户' , 404);
                }
                break;
            case 'group':
                $group = GroupModel::findById($target_id);
                if (empty($group)) {
                    return self::error('创建会话失败！群不存在' , 404);
                }
                $member = GroupMemberModel::findByUserIdAndGroupId($user_id , $target_id);
                if (empty($member)) {
                    return self::error('创建会话失败！您不是群成员' , 403);
                }
                break;
            case 'system':
                break;
            default:
                return self::error('不支持的会话类型');
        }
        $session_id = '';
        if ($type == 'system') {
            $session = SessionModel::findByUserIdAndType($user_id , $type);
        } else {
            $session_id = ChatUtil::sessionId($type , $target_id);
            $session = SessionModel::findByUserIdAndTypeAndTargetId($user_id , $type , $target_id);
        }
        // 检查会话是否存在
        if (empty($session)) {
            $id = SessionModel::insertGetId([
                'identifier' => $identifier ,
                'user_id'   => $user_id ,
                'type'      => $type ,
                'target_id' => $target_id ,
                'session_id' => $session_id ,
            ]);
        } else {
            SessionModel::updateById($session->id , [
                'update_time' => date('Y-m-d H:i:s') ,
            ]);
        }
        return self::success();
    }
}