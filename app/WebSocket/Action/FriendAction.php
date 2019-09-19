<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/11
 * Time: 15:07
 */

namespace App\WebSocket\Action;


use App\Model\Application;
use App\Model\FriendModel;
use App\Model\Message;
use App\Model\UserModel;
use App\Util\ChatUtil;
use App\Util\PushUtil;
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
        $friend = UserModel::findById($param['friend_id']);
        if (empty($friend)) {
            return self::error('用户不存在' , 404);
        }
        if (FriendModel::isFriend($auth->user->id , $param['friend_id'])) {
            return self::error('已经是好友！' , 403);
        }
        $param['type']      = 'private';
        $param['op_type']   = 'app_friend';
        $param['user_id']   = $param['friend_id'];
        $param['relation_user_id'] = $auth->user->id;
        try {
            DB::beginTransaction();
            if (!$friend->user_option->friend_auth) {
                $param['status'] = 'approve';
                // 未开启好友验证
                FriendModel::u_insertGetId($auth->user->id , $param['friend_id']);
                FriendModel::u_insertGetId($param['friend_id'] , $auth->user->id);
            } else {
                $param['status'] = 'wait';
            }
            Application::insertGetId(array_unit($param , [
                'type' ,
                'op_type' ,
                'user_id' ,
                'relation_user_id' ,
                'status' ,
                'remark' ,
            ]));
//            DB::commit();
            if ($friend->user_option->friend_auth) {
                // 推送申请数量更新
                PushUtil::single($auth->identifier , $param['friend_id'] , 'refresh_application');
                // 推送总未读消息数量更新
                PushUtil::single($auth->identifier , $param['friend_id'] , 'refresh_unread_count');
                // todo app推送
            }
            return self::success('操作成功');
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function decideApp(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'application_id' => 'required' ,
            'status'         => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $range = config('business.application_status_for_user');
        if (!in_array($param['status'] , $range)) {
            return self::error('不支持的 status 值，当前受支持的值有 ' . implode(',' , $range) , 403);
        }
        $app = Application::findById($param['application_id']);
        if (empty($app)) {
            return self::error('未找到对应的申请记录' , 404);
        }
        if ($app->type != 'private') {
            return self::error('该申请记录类型不是私聊！非法操作' , 403);
        }
        try {
            DB::beginTransaction();
            Application::updateById($app->id , [
                'status' => $param['status']
            ]);
            if ($param['status'] == 'approve') {
                // 同意
                FriendModel::u_insertGetId($app->user_id , $app->relation_user_id);
                FriendModel::u_insertGetId($app->relation_user_id , $app->user_id);
            }
            DB::commit();
            if ($param['status'] == 'approve') {
                // todo app推送
            }
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
        if (!FriendModel::isFriend($auth->user->id , $param['friend_id'])) {
            return self::error('你们并非好友，无权限操作' , 403);
        }
        $chat_id = ChatUtil::chatId($auth->user->id , $param['friend_id']);
        try {
            DB::beginTransaction();
            // 删除好友
            FriendModel::delByUserIdAndFriendId($auth->user->id , $param['friend_id']);
            FriendModel::delByUserIdAndFriendId($param['friend_id'] , $auth->user->id);
            // 删除聊天记录
            Message::delByChatId($chat_id);
            DB::commit();
            return self::success();
        } catch(Exception $e) {
            DB::rollBack();
            return self::error((new Throwable())->exceptionJsonHandlerInDev($e , true) , 500);
        }
    }
}