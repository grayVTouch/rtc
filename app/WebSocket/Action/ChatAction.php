<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/12
 * Time: 10:21
 */

namespace App\WebSocket\Action;

use App\Model\FriendModel;
use App\Model\MessageModel;
use App\Model\MessageReadStatusModel;
use App\Model\GroupModel;
use App\Model\GroupMemberModel;
use App\Model\GroupMessageModel;
use App\Model\GroupMessageReadStatusModel;
use App\Redis\MessageRedis;
use App\Redis\UserRedis;
use App\Util\ChatUtil;
use App\WebSocket\Util\MessageUtil;
use App\WebSocket\Util\UserUtil;
use function core\array_unit;
use Core\Lib\Throwable;
use Core\Lib\Validator;
use Exception;
use Illuminate\Support\Facades\DB;
use App\WebSocket\Auth;


class ChatAction extends Action
{
    /**
     * 消息发送-平台咨询-文本
     *
     * @param Auth $auth
     * @param array $param
     * @return array
     */
    public static function advoise(Auth $auth , $type , array $param)
    {
        $param['user_id']   = $auth->user->id;
        $param['type']      = $type;
        return ChatUtil::advoise($auth , $param);
    }

    /**
     * 私聊消息发送
     *
     * @throws \Exception
     */
    public static function send(Auth $auth , $type , array $param = [])
    {
        $param['user_id']   = $auth->user->id;
        $param['type']      = $type;
        return ChatUtil::send($auth , $param);
    }

    /**
     * 群消息发送
     *
     * @throws \Exception
     */
    public static function groupSend(Auth $auth , $type , array $param = [])
    {
        $param['user_id']   = $auth->user->id;
        $param['type']      = $type;
        return ChatUtil::groupSend($auth , $param);
    }


}