<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/20
 * Time: 16:06
 */

namespace App\Http\Web\Controller;


use App\Data\UserData;
use App\Model\UserTokenModel;
use App\Redis\UserRedis;
use App\Util\MiscUtil;
use App\Util\PushUtil;
use Engine\Facade\WebSocket;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Util\UserUtil as BaseUserUtil;

class Authorization extends Base
{
    // pc 端登录页面授权
    public function auth()
    {
        $param = $this->request->get;
        $param['client_id'] = $param['client_id'] ?? '';
        if (empty($param['client_id'])) {
            return $this->error('请提供 client_id') ;
        }
        // 检查该客户端 id 是否在线
        if (!WebSocket::exist($param['client_id'])) {
            return $this->error('客户端已经离线');
        }
        $user_id = '600027';
        $user = UserData::findByIdentifierAndId('nimo' , $user_id);
        WebSocket::push($param['client_id'] , json_encode([
            'type' => 'avatar' ,
            'data' => $user->avatar
        ]));
        var_dump('ws 推送 ');

        sleep(5);
        $param['platform'] = 'web';
        $param['identifier'] = 'nimo';
        $param['user_id'] = $user->id;
        $param['token']  = MiscUtil::token();
        $param['expire'] = date('Y-m-d H:i:s' , time() + config('app.timeout'));
        try {
            DB::beginTransaction();
            // 先检查当前登录平台是否是非 pc 浏览器
            // 如果时非 pc 浏览器，那么将其他有效的 token 删除
            // 或让其等价于 无效
            // 这是为了保证 同一平台仅 允许 单个设备登录
            $single_device_for_platform = config('business.single_device_for_platform');
            if (in_array($param['platform'] , $single_device_for_platform)) {
                // 删除掉其他 token
                UserTokenModel::delByUserIdAndPlatform($user->id, $param['platform']);
            }
            UserRedis::fdMappingPlatform($param['identifier'], $param['client_id'], $param['platform']);
            UserTokenModel::u_insertGetId($param['identifier'], $param['user_id'], $param['token'], $param['expire'], $param['platform']);
            // 上线通知
            $online = UserRedis::isOnline($param['identifier'], $user->id);
            BaseUserUtil::mapping($param['identifier'], $user->id, $param['client_id']);
            if (!$online) {
                // 之前如果不在线，现在上线，那么推送更新
                BaseUserUtil::onlineStatusChange($param['identifier'], $param['user_id'], 'online');
            }
            DB::commit();
            if (in_array($param['platform'], $single_device_for_platform)) {
                // 通知其他客户端你已经被迫下线
                $client_ids = UserRedis::userIdMappingFd($param['identifier'], $user->id);
                foreach ($client_ids as $v) {
                    // 检查平台
                    $platform = UserRedis::fdMappingPlatform($param['identifier'], $v);
                    if (!in_array($platform, $single_device_for_platform)) {
                        continue;
                    }
                    if ($v == $param['client_id']) {
                        // 跳过当前用户
                        continue;
                    }
                    // 通知对方下线
                    PushUtil::single($param['identifier'], $v, 'forced_offline');
                }
            }
            WebSocket::push($param['client_id'] , json_encode([
                'type' => 'logined' ,
                'data' => [
                    'user_id' => 600027 ,
                    'token' => $param['token'] ,
                ] ,
            ]));
            var_dump('ws 推送 logined');
            return $this->success('操作成功');
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}