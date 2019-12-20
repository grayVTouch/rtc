<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/19
 * Time: 14:30
 */

namespace App\Http\Web\Controller;


use App\Redis\CacheRedis;
use Core\Lib\Validator;
use function core\random;
use Engine\Facade\WebSocket;
use GeetestLib;

class GTValidator extends Common
{
    public function genValidateSession()
    {
        $gt_id = config('app.gt_id');
        $gt_key = config('app.gt_key');
        // 生成一个网站用户id
        $user_id_for_gt = random(12 , 'mixed' , true);
        $client_ip  = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $gt_validator = new GeetestLib($gt_id, $gt_key);
        $data = [
            // 网站用户id
            "user_id"       => $user_id_for_gt ,
            // web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
            "client_type"   => "web" ,
            // 请在此处传输用户请求验证时所携带的IP
            "ip_address"    => $client_ip
        ];
        $res = $gt_validator->pre_process($data, 1);
        $gt_res = $gt_validator->get_response();
        CacheRedis::value('gt_' . $gt_res['challenge'] , json_encode([
            'user_id'       => $user_id_for_gt ,
            'client_type'   => 'web' ,
            'ip'            => $client_ip ,
            'gt_server'     => (int) $res

        ]));
        // 个人觉得这个地方实在是没什么用
        //
//        WebSocket::push($this->request->fd , json_encode([
//            'type' => 'gt' ,
//            'data' => $gt_res['challenge'] ,
//        ]));
        return $this->rawResponse(json_encode($gt_res));
    }

    public function gtResponse($status)
    {
        return json_encode([
            'status' => $status
        ]);
    }

    public function validateSession()
    {
        print_r(file_get_contents('php://input'));
        $param = $this->request->post;
        $param['geetest_challenge'] = $param['geetest_challenge'] ?? '';
        $param['geetest_validate'] = $param['geetest_validate'] ?? '';
        $param['geetest_seccode'] = $param['geetest_seccode'] ?? '';
        $validator = Validator::make($param , [
            'geetest_challenge' => 'required' ,
            'geetest_validate' => 'required' ,
            'geetest_seccode' => 'required' ,
        ]);
        if ($validator->fails()) {
            var_dump("gt 验证失败 400：" . $validator->message());
            return $this->rawResponse($this->gtResponse('fail'));
        }
        // 获取 redis 数据
        $gt_cache_key = 'gt_' . $param['geetest_challenge'];
        $cache = CacheRedis::value($gt_cache_key);
        if (empty($cache)) {
            var_dump("gt 验证失败 400，未找到 challenge: " . $param['geetest_challenge'] . ' 对应 redis key');
            return $this->rawResponse($this->gtResponse('fail'));
        }
        $cache = json_decode($cache);
        var_dump("gt redis 缓存的数据：");
        print_r($cache);
        $gt_id = config('app.gt_id');
        $gt_key = config('app.gt_key');
        $gt_validator = new GeetestLib($gt_id, $gt_key);
        $validate_res = 'error';
        if ($cache->gt_server == 1) {
            // 服务器正常
            $client_ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $res = $gt_validator->success_validate($param['geetest_challenge'], $param['geetest_validate'], $param['geetest_seccode'], [
                'user_id'       => $cache->user_id ,
                'client_type'   => $cache->client_type ,
                'ip_address'    => $client_ip
            ]);
            if ($res) {
                $validate_res = 'success';
            } else {
                $validate_res = 'error';
            }
        } else {
            if ($gt_validator->fail_validate($param['geetest_challenge'],$param['geetest_validate'],$param['geetest_seccode'])) {
                $validate_res = 'success';
            } else {
                $validate_res = 'error';
            }
        }
        // 删除缓存的 redis key
        CacheRedis::del($gt_cache_key);
//        CacheRedis::value('gt_check_res_' . $param['geetest_challenge'] , $validate_res);
        var_dump('验证结果: ' . $validate_res);
        return $this->rawResponse($this->gtResponse($validate_res == 'success' ? 'success' : 'fail'));
    }
}