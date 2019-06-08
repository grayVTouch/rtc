<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/22
 * Time: 10:48
 */

namespace App\WebSocket\Action;


use App\Model\Push;
use App\Model\PushReadStatus;
use App\WebSocket\Auth;
use Core\Lib\Validator;
use function extra\array_unit;

class PushAction extends Action
{
    // 未读消息数量
    public static function unread(Auth $auth , array $param)
    {
        $res = PushReadStatus::unreadByUserId($auth->user->id , config('app.limit'));
        return self::success($res);
    }

    // 设置：读取状态
    public static function readStatus(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'push_id'      => 'required' ,
            'is_read'      => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->error());
        }
        // 检查是否已经存在
        $param['user_id'] = $auth->user->id;
        $res = PushReadStatus::findByUserIdAndPushId($auth->user->id , $param['push_id']);
        if (empty($res)) {
            $id = PushReadStatus::u_insertGetId($param['user_id'] , $param['push_id'] , $param['is_read']);
        } else {
            PushReadStatus::updateById($res->push_id , array_unit($param , [
                'is_read'
            ]));
            $id = $res->id;
        }
        return self::success($id);
    }
}