<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/22
 * Time: 10:48
 */

namespace App\WebSocket\V1\Action;


use App\WebSocket\V1\Model\PushModel;
use App\WebSocket\V1\Model\PushReadStatusModel;
use App\WebSocket\V1\Controller\Auth;
use Core\Lib\Validator;
use function core\array_unit;
use Illuminate\Support\Facades\DB;

class PushAction extends Action
{
    // 未读消息数量
    public static function unread(Auth $auth , array $param)
    {
        $res = PushModel::unreadByUserId($auth->user->id , config('app.limit'));
        return self::success($res);
    }

    // 设置：读取状态
    public static function updateIsRead(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'push_id'      => 'required' ,
            'is_read'      => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $bool_for_int = config('business.bool_for_int');
        if (!in_array($param['is_read'] , $bool_for_int)) {
            return self::error('不支持的 is_read 值，受支持的值有 ' . implode(',' , $bool_for_int));
        }
        PushReadStatusModel::updateIsReadByUserIdAndPushId($auth->user->id , $param['push_id'] , $param['is_read']);
        // 刷新会话
        $auth->push($auth->user->id , 'refresh_session');
        $auth->push($auth->user->id , 'refresh_unread_count');
        $auth->push($auth->user->id , 'refresh_session_unread_count');
        return self::success();
    }

    public static function myPush(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'type' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $push_type_for_push = config('business.push_type_for_push');
        if (!in_array($param['type'] , $push_type_for_push)) {
            return self::error('不支持的推送类型，当前受支持的推送类型有：' . implode(',' , $push_type_for_push));
        }
        $limit = empty($param['limit']) ? config('app.limit') : $param['limit'];
        $res = PushModel::getByUserIdAndTypeAndLimitIdAndLimit($auth->user->id , $param['type'] , $param['limit_id'] , $limit);
        return self::success($res);
    }

    public static function latest(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'type' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $push_type_for_push = config('business.push_type_for_push');
        if (!in_array($param['type'] , $push_type_for_push)) {
            return self::error('不支持的推送类型，当前受支持的推送类型有：' . implode(',' , $push_type_for_push));
        }
        $res = PushModel::latestByUserIdAndTypeAndLimitId($auth->user->id , $param['type'] , $param['limit_id']);
        return self::success($res);
    }

    public static function resetUnread(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'type' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }

        $push_type = config('business.push_type_for_push');
        if (!in_array($param['type'] , $push_type)) {
            return self::error('不支持的 type，当前受支持的 type 有' . implode(' , ' , $push_type));
        }
        PushReadStatusModel::updateIsReadByUserIdAndType($auth->user->id , $param['type'] , 1);
        $auth->push($auth->user->id , 'refresh_session');
        $auth->push($auth->user->id , 'refresh_unread_count');
        $auth->push($auth->user->id , 'refresh_session_unread_count');
        return self::success();
    }
}