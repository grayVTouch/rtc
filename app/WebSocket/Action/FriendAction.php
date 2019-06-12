<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/11
 * Time: 15:07
 */

namespace App\WebSocket\Action;


use App\Model\Application;
use App\Model\Friend;
use App\Model\Message;
use App\Util\Chat;
use App\WebSocket\Auth;
use Core\Lib\Throwable;
use Core\Lib\Validator;
use Exception;
use function extra\array_unit;
use Illuminate\Support\Facades\DB;

class FriendAction extends Action
{
    public static function appJoinFriend(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'friend_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $param['type']      = 'private';
        $param['op_type']   = 'add';
        $param['user_id']   = $param['friend_id'];
        $param['relation_user_id'] = $auth->user->id;
        $param['status']    = 'wait';
        $id = Application::insertGetId(array_unit($param , [
            'type' ,
            'op_type' ,
            'user_id' ,
            'relation_user_id' ,
            'status' ,
            'remark' ,
        ]));
        // todo 看情况是否要加一个推送
        return self::success($id);
    }

    public static function decideApp(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'application_id' => 'required' ,
            'status' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $range = config('business.application_status_for_user');
        if (!in_array($param['status'] , $range)) {
            return self::error('不支持的 status 值，当前受支持的值有 ' . implode(',' , $range));
        }
        $app = Application::findById($param['application_id']);
        if (empty($app)) {
            return self::error('未找到对应的申请记录' , 404);
        }
        if ($app->type != 'private') {
            return self::error('该申请记录类型不是 私聊！禁止操作' , 403);
        }
        try {
            DB::beginTransaction();
            Application::updateById($app->id , [
                'status' => $param['status']
            ]);
            if ($param['status'] == 'approve') {
                // 同意
                Friend::u_insertGetId($app->user_id , $app->relation_id);
                Friend::u_insertGetId($app->relation_id , $app->user_id);
            }
            DB::commit();
            return self::success();
        } catch(Exception $e) {
            DB::rollBack();
            return self::error((new Throwable())->exceptionJsonHandlerInDev($e , true) , 500);
        }
    }

    public static function delFriend(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'friend_id' => 'required'
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        if (Friend::isFriend($auth->user->id , $param['friend_id'])) {
            return self::error('你们并非好友，无权限操作' , 403);
        }
        $chat_id = Chat::chatId($auth->user->id , $param['friend_id']);
        try {
            DB::beginTransaction();
            Friend::delByUserIdAndFriendId($auth->user->id , $param['friend_id']);
            // 删除对应的聊天记录
            Message::delByChatId($chat_id);
            DB::commit();
            return self::success();
        } catch(Exception $e) {
            DB::rollBack();
            return self::error((new Throwable())->exceptionJsonHandlerInDev($e , true) , 500);
        }
    }
}