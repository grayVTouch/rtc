<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/20
 * Time: 11:52
 */

namespace App\WebSocket\Action;


use App\Model\FriendModel;
use App\Model\GroupMemberModel;
use App\Model\GroupMessageModel;
use App\Model\GroupMessageReadStatusModel;
use App\Model\GroupModel;
use App\Model\MessageModel;
use App\Model\SessionModel;
use App\Model\UserModel;
use App\Redis\SessionRedis;
use App\Util\ChatUtil;
use App\Util\GroupUtil;
use App\Util\MiscUtil;
use App\Util\PageUtil;
use App\Util\SessionUtil;
use App\Util\UserUtil;
use App\WebSocket\Auth;
use App\WebSocket\Util\MessageUtil;
use function core\array_unit;
use Core\Lib\Validator;
use function core\obj_to_array;
use Exception;
use Illuminate\Support\Facades\DB;

class SessionAction extends Action
{

    public static function session(Auth $auth , array $param)
    {
//        $top_session = SessionModel::topSessionByUserId($auth->user->id);
//        $no_top_total  = SessionModel::noTopCountByUserId($auth->user->id);
//        $no_top_page   = PageUtil::deal($no_top_total , $param['page'] , $param['limit']);
//        $general_session = SessionModel::noTopGetByUserIdAndOffsetAndLimit($auth->user->id , $no_top_page['offset'] , $no_top_page['limit']);
        $top_session = [];
        $general_session = [];
        $sessions = SessionModel::getByUserId($auth->user->id);
        foreach ($sessions as $v)
        {
            if ($v->type == 'private') {
                $other_id = ChatUtil::otherId($v->target_id , $auth->user->id);
                $v->other = UserModel::findById($other_id);
                UserUtil::handle($v->other , $auth->user->id);
                $recent_message = MessageModel::recentMessage($auth->user->id , $v->target_id);
                MessageUtil::handleMessage($recent_message , $v->user_id , $other_id);
                // 私聊消息处理
                $v->recent_message = $recent_message;
                $v->unread = MessageModel::countByChatIdAndUserIdAndIsRead($v->target_id , $v->user_id , 0);
                if ($v->top == 1) {
                    // 置顶群聊
                    $top_session[] = $v;
                    continue ;
                }
                $general_session[] = $v;
                continue ;
            }

            // 群聊
            $v->group = GroupModel::findById($v->target_id);
            GroupUtil::handle($v->group , $auth->user->id);
            $recent_message = GroupMessageModel::recentMessage($auth->user->id , $v->target_id , 'none');
            // 群处理
            $v->recent_message = $recent_message;
            // 群消息处理
            MessageUtil::handleGroupMessage($v->recent_message);
            if ($auth->user->role == 'user' && $v->group->is_service == 1) {
                // 用户使用的平台
                $v->group->name = '平台咨询';
            }
            $v->unread = GroupMessageReadStatusModel::countByUserIdAndGroupId($auth->user->id , $v->target_id , 0);
            if ($v->top == 1) {
                $top_session[] = $v;
                continue ;
            }
            $general_session[] = $v;
        }
        $res = array_merge($top_session , $general_session);
        return self::success($res);
    }

    public static function top(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'type'      => 'required' ,
            'top'       => 'required' ,
            'target_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $exist = SessionModel::exist($auth->user->id , $param['type'] , $param['target_id']);
        if ($exist) {
            // 更改类型
            SessionModel::updateByUserIdAndTypeAndTargetId($auth->user->id , $param['type'] , $param['target_id'] , $param['top']);
        } else {
            $param['user_id'] = $auth->user->id;
            $id = SessionModel::insertGetId(array_unit($param , [
                'user_id' ,
                'type' ,
                'target_id' ,
                'top' ,
            ]));
            // var_dump($id);
        }
        // 刷新会话
        $auth->push($auth->user->id , 'refresh_session');
        return self::success();
    }

    public static function createOrUpdate(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'type'      => 'required' ,
            'target_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $param['user_id'] = $auth->user->id;
        $param['top'] = $param['top'] == '' ? 0 : $param['top'];
        $res = SessionUtil::createOrUpdate($auth->user->id , $param['type'] , $param['target_id'] , $param['top']);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        $auth->push($auth->user->id , 'refresh_session');
        return self::success();
    }

    // 删除会话
    public static function delete(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'id_list'      => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $id_list = json_decode($param['id_list'] , true);
        if (empty($id_list)) {
            return self::error('请提供待删除的会话');
        }
        // 检查所有的会话是否包含他人的会话
        if (SessionModel::existOtherByIds($auth->user->id , $id_list)) {
            return self::error('包含他人的会话，禁止操作' , 403);
        }
        try {
            DB::beginTransaction();
            foreach ($id_list as $v)
            {
                $res = SessionUtil::delete($v);
                if ($res['code'] != 200) {
                    DB::rollBack();
                    return self::error($res['data'] , $res['code']);
                }
            }
            DB::commit();
            return self::success();
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // 会话处理
    public static function sessionProcess(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'type'      => 'required' ,
            'target_id' => 'required' ,
            'status'    => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $type_range = config('business.session_type');
        if (!in_array($param['type'] , $type_range)) {
            return self::error('不支持的会话类型' . implode(' , ' , $type_range));
        }
        $session_process_status = config('business.session_process_status');
        if (!in_array($param['status'] , $session_process_status)) {
            return self::error('不支持的会话处理状态' . implode(' , ' , $type_range));
        }
        $session_id = ChatUtil::sessionId($param['type'] , $param['target_id']);
        switch ($param['status'])
        {
            case 'join':
                SessionRedis::sessionMember($auth->identifier , $session_id , $auth->user->id);
                break;
            case 'leave':
                SessionRedis::delSessionMember($auth->identifier , $session_id , $auth->user->id);
                break;
        }
        return self::success();
    }
}