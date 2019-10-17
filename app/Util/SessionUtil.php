<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/10/17
 * Time: 10:26
 */

namespace App\Util;


use App\Model\SessionModel;
use function core\array_unit;

class SessionUtil extends Util
{
    // 创建 或 更新会话
    public static function createOrUpdate(int $user_id , string $type , $target_id , int $top = 0)
    {
        // 检查 type 是否正确
        $type_range = config('business.session_type');
        if (!in_array($type , $type_range)) {
            return self::error('不支持的 type，当前受支持的 type 有' . implode(' , ' , $type_range));
        }
        $session_id = ChatUtil::sessionId($type , $target_id);
        $session = SessionModel::findByUserIdAndTypeAndTargetId($user_id , $type , $target_id);
        // 检查会话是否存在
        if (empty($session)) {
            $id = SessionModel::insertGetId([
                'user_id'   => $user_id ,
                'type'      => $type ,
                'target_id' => $target_id ,
                'session_id' => $session_id ,
                'top'       => $top ,
            ]);
            // var_dump("session_id {$id}");
        }
        SessionModel::updateById($session->id , [
            'update_time' => date('Y-m-d H:i:s') ,
        ]);
        return self::success();
    }
}