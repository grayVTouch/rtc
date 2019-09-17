<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/12
 * Time: 9:39
 */

namespace App\WebSocket\Action;

use App\Model\Group;
use App\Model\User;
use App\Model\UserToken;
use App\Redis\UserRedis;
use App\Util\Misc;
use App\WebSocket\Util\UserUtil;
use function core\array_unit;
use Core\Lib\Validator;
use App\WebSocket\Base;

class LoginAction extends Action
{
    public static function login(Base $base , array $param)
    {
        if (!config('app.enable_guest')) {
            // 未启用旅客模式
            $validator = Validator::make($param , [
                'unique_code'    => 'required' ,
            ] , [
                'unique_code.required' => '必须' ,
            ]);
            if ($validator->fails()) {
                return self::error($validator->error());
            }
            $user = User::findByUniqueCode($param['unique_code']);
            if (empty($user)) {
                return self::error([
                    'unique_code' => '未找到当前提供的 unique_code 对应的用户' ,
                ]);
            }
        } else {
            // 旅客模式
            $user = User::findByUniqueCode($param['unique_code']);
            if (empty($user)) {
                // 自动分配用户
                $user = UserUtil::createTempUser($base->identifier);
                if (empty($user)) {
                    return self::error('创建访客账号失败' , 500);
                }
                // 给当前登录用户推送一条登录
                $base->clientPush('unique_code' , $user->unique_code);
            }
        }
        // 登录成功
        $param['identifier'] = $user->identifier;
        $param['user_id'] = $user->id;
        $param['token']  = Misc::token();
        $param['expire'] = date('Y-m-d H:i:s' , time() + config('app.timeout'));
        UserToken::u_insertGetId($param['identifier'] , $param['user_id'] , $param['token'] , $param['expire']);
        // 绑定 user_id <=> fd
//        var_dump('当前登录的客户端链接 fd：' . $base->fd);
        UserRedis::fdByUserId($base->identifier , $user->id , $base-fd);
        UserRedis::fdMappingUserId($base->identifier , $base->fd , $user->id);
        if ($user->role == 'admin') {
            // 工作人员
            // 登录成功后消费未读游客消息（平台咨询）队列
            UserUtil::consumeUnhandleMsg($user);
        } else {
            // 平台用户
            // 初始化咨询通道
            UserUtil::initAdvoiseGroup($user->id);
            // 自动分配客服
            $group = Group::advoiseGroupByUserId($user->id);
            // 检查是否分配过客服
            $bind_waiter = UserRedis::groupBindWaiter($base->identifier , $group->id);
            if (empty($bind_waiter)) {
                $res = UserUtil::allocateWaiter($user->id);
                if ($res['code'] != 200) {
                    var_dump($res['data']);
                    UserUtil::noWaiterTip($base->identifier , $user->id , $group->id);
                }
            }
        }
        // 推送一条未读消息数量
        return self::success($param['token']);
    }
}