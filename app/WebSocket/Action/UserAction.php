<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/19
 * Time: 21:54
 */

namespace App\WebSocket\Action;


use App\Model\ApplicationModel;
use App\Model\GroupModel;
use App\Model\UserModel;
use App\Model\UserOptionModel;
use App\Redis\UserRedis;
use App\Util\PageUtil;
use App\WebSocket\Auth;
use App\Util\UserUtil;
use function core\array_unit;
use Core\Lib\Validator;
use function WebSocket\ws_config;

class UserAction extends Action
{
    // 咨询通道绑定的群信息
    public static function groupForAdvoise(Auth $auth , array $param)
    {
        $group = GroupModel::advoiseGroupByUserId($auth->user->id);
        return self::success($group);
    }

    // 申请记录
    public static function app(Auth $auth , array $param)
    {
        $param['page'] = empty($param['page']) ? ws_config('app.page') : $param['page'];
        $param['limit'] = empty($param['limit']) ? ws_config('app.limit') : $param['limit'];
        $total = ApplicationModel::countByUserId($auth->user->id);
        $page = PageUtil::deal($total , $param['page'] , $param['limit']);
        $res = ApplicationModel::listByUserId($auth->user->id , $page['offset'] , $param['limit']);
        foreach ($res as $v)
        {
            UserUtil::handle($v->user);
        }
        $res = PageUtil::data($page , $res);
        return self::success($res);
    }

    public static function editUserInfo(Auth $auth , array $param)
    {
        $param['avatar'] = empty($param['avatar']) ? $auth->user->avatar : $param['avatar'];
        $param['sex'] = empty($param['sex']) ? $auth->user->sex : $param['sex'];
        $param['birthday'] = empty($param['birthday']) ? $auth->user->birthday : $param['birthday'];
        $param['nickname'] = empty($param['nickname']) ? $auth->user->nickname : $param['nickname'];
        $param['signature'] = empty($param['signature']) ? $auth->user->signature : $param['signature'];
        UserModel::updateById($auth->user->id , array_unit($param , [
            'avatar' ,
            'sex' ,
            'birthday' ,
            'nickname' ,
            'signature' ,
        ]));
        return self::success();
    }

    // 搜索好友
    public static function search(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'keyword' => 'required'
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $res = [];
        if ($user_use_id = UserModel::findById($param['keyword'])) {
            UserUtil::handle($user_use_id);
            $res[] = $user_use_id;
        }
        if ($user_use_nickname = UserModel::findByIdentifierAndNickname($auth->identifier , $param['keyword'])) {
            UserUtil::handle($user_use_nickname);
            $res[] = $user_use_nickname;
        }
        if ($user_use_phone = UserModel::findByIdentifierAndPhone($auth->identifier , $param['keyword'])) {
            UserUtil::handle($user_use_phone);
            $res[] = $user_use_phone;
        }
        return self::success($res);
    }

    // 用户信息
    public static function user(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'user_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $user = UserModel::findById($param['user_id']);
        if (empty($user)) {
            return self::error('未找到用户' , 404);
        }
        UserUtil::handle($user);
        return self::success($user);
    }

    public static function mapping(Auth $auth , array $param)
    {
        UserRedis::userIdMappingFd($auth->identifier , $auth->user->id , $auth->fd);
        UserRedis::fdMappingUserId($auth->identifier , $auth->fd , $auth->user->id);
        return self::success();
    }

    public static function info(Auth $auth , array $param)
    {
        return self::success($auth->user);
    }

    // 修改用户选项信息
    public static function editUserOption(Auth $auth , array $param)
    {
        $user_option = UserOptionModel::findByUserId($auth->user->id);
        if (empty($user_option)) {
            return self::error('没有找到用户选项信息，数据不完整！请联系开发人员' , 404);
        }
        $param['private_notification']  = empty($param['private_notification']) ? $user_option->private_notification : $param['private_notification'];
        $param['group_notification']    = empty($param['group_notification']) ? $user_option->group_notification : $param['group_notification'];
        $param['write_status']          = empty($param['write_status']) ? $user_option->write_status : $param['write_status'];
        $param['friend_auth']           = empty($param['friend_auth']) ? $user_option->friend_auth : $param['friend_auth'];
        UserOptionModel::updateById($user_option->id , array_unit($param , [
            'private_notification' ,
            'group_notification' ,
            'write_status' ,
            'friend_auth' ,
        ]));
        return self::success();
    }
}