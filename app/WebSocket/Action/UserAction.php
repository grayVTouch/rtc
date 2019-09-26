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
use App\WebSocket\Auth;
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
        $param['user_id'] = $auth->user->id;
        $param['limit'] = empty($param['limit']) ? ws_config('app.limit') : $param['limit'];
        $order = parse_order($param['order']);
        $res = ApplicationModel::list($param , $order , $param['limit']);
        return self::success($res);
    }

    public static function editUserInfo(Auth $auth , array $param)
    {
        $param['avatar'] = empty($param['avatar']) ? $auth->user->avatar : $param['avatar'];
        $param['sex'] = empty($param['sex']) ? $auth->user->sex : $param['sex'];
        $param['birthday'] = empty($param['birthday']) ? $auth->user->birthday : $param['birthday'];
        $param['nickname'] = empty($param['nickname']) ? $auth->user->nickname : $param['nickname'];
        $param['signature'] = empty($param['signature']) ? $auth->user->signature : $param['signature'];
        UserModel::updateById($auth->user->id , [
            'avatar' ,
            'sex' ,
            'birthday' ,
            'nickname' ,
            'signature' ,
        ]);
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
            $res[] = $user_use_id;
        }
        if ($user_use_phone = UserModel::findByIdentifierAndPhone($auth->identifier , $param['keyword'])) {
            $res[] = $user_use_phone;
        }
        return self::success($res);
    }
}