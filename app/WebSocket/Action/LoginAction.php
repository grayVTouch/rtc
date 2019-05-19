<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/12
 * Time: 9:39
 */

namespace App\WebSocket\Action;


use App\Model\GroupMember;
use App\Model\User;
use App\Model\UserToken;
use App\Redis\MessageRedis;
use App\Redis\UserRedis;
use function core\array_unit;
use Core\Lib\Validator;
use function core\ssl_random;

use App\WebSocket\Base;
use Exception;
use Illuminate\Support\Facades\DB;

class LoginAction extends Action
{
    public static function login(Base $app , array $param)
    {
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
        // 登录成功
        $param['identifier'] = $user->identifier;
        $param['user_id'] = $user->id;
        $param['token']  = token();
        $param['expire'] = date('Y-m-d H:i:s' , time() + config('app.timeout'));
        UserToken::insert(array_unit($param , [
            'token' ,
            'expire' ,
            'user_id' ,
            'identifier' ,
        ]));
        // 绑定 user_id <=> fd
        UserRedis::fdByUserId($app->identifier , $user->id , $app->fd);
        UserRedis::fdMappingUserId($app->identifier , $app->fd , $user->id);
        // 登录成功后消费未读游客消息（平台咨询）队列
        self::util_allocate($app , $user);
        return self::success($param['token']);
    }

    public static function util_allocate(Base $app , User $user)
    {
        if (empty($user)) {
            return ;
        }
        if ($user->role != 'admin') {
            // 不是工作人员
            return ;
        }
        try {
            DB::beginTransaction();
            $group_msg = MessageRedis::consumeUnhandleMsg($app->identifier);
            foreach ($group_msg as $v)
            {
                if (empty(GroupMember::findByUserIdAndGroupId($user->id , $v['group_id']))) {
                    GroupMember::insert([
                        'user_id' => $user->id ,
                        'group_id' => $v['group_id']
                    ]);
                    $user_ids = GroupMember::getUserIdByGroupId($v['group_id']);
                    $app->pushAll($user_ids , 'refresh_session');
                }
                // 绑定活跃群组
                UserRedis::groupBindWaiter($app->identifier , $v['group_id'] , $user->id);
                UserRedis::delNoWaiterForGroup($app->identifier , $v['group_id']);
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $app->push($user->id , 'error' , $e);
        }
    }
}