<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/18
 * Time: 16:09
 */

namespace App\Http\Web;


use GeetestLib;

class Test extends Base1
{

    public function genValidateSession()
    {
        // 生成一个网站用户id
//        $user_id_for_gt = random(12 , 'mixed' , true);
        $client_ip = '127.0.0.1';
        $GtSdk = new GeetestLib(CAPTCHA_ID, PRIVATE_KEY);
        $data = [
            // 网站用户id
            "user_id"       => 'abcdefg' ,
            // web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
            "client_type"   => "web" ,
            // 请在此处传输用户请求验证时所携带的IP
            "ip_address"    => $client_ip
        ];
        $_SESSION['gtserver'] = $GtSdk->pre_process($data, 1);

        // 设置响应头
        $this->response->header('Content-Type' , 'application/json');

        // 允许跨域
        $this->response->header('Access-Control-Allow-Origin' , '*');
        $this->response->header('Access-Control-Allow-Methods' , 'GET,POST,PUT,PATCH,DELETE');
        $this->response->header('Access-Control-Allow-Credentials' , 'false');
        $this->response->header('Access-Control-Allow-Headers' , 'Authorization,Content-Type,X-Request-With,Ajax-Request');

        $this->response->status(200);
        return $this->response->end($GtSdk->get_response_str());
    }

    public function validateSession()
    {
        $GtSdk = new GeetestLib(CAPTCHA_ID, PRIVATE_KEY);
        $data = array(
            "user_id" => 'abcdefg' , # 网站用户id
            "client_type" => "web", #web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
            "ip_address" => "127.0.0.1" # 请在此处传输用户请求验证时所携带的IP
        );
        if ($_SESSION['gtserver'] == 1) {   //服务器正常
            $result = $GtSdk->success_validate($this->request->post['geetest_challenge'], $this->request->post['geetest_validate'], $this->request->post['geetest_seccode'], $data);
            if ($result) {
                $response = '{"status":"success"}';
            } else {
                $response = '{"status":"fail"}';
            }
        } else {  //服务器宕机,走failback模式
            if ($GtSdk->fail_validate($this->request->post['geetest_challenge'],$this->request->post['geetest_validate'],$this->request->post['geetest_seccode'])) {
                $response = '{"status":"success"}';
            } else {
                $response = '{"status":"fail"}';
            }
        }
        // 设置响应头
        $this->response->header('Content-Type' , 'application/json');

        // 允许跨域
        $this->response->header('Access-Control-Allow-Origin' , '*');
        $this->response->header('Access-Control-Allow-Methods' , 'GET,POST,PUT,PATCH,DELETE');
        $this->response->header('Access-Control-Allow-Credentials' , 'false');
        $this->response->header('Access-Control-Allow-Headers' , 'Authorization,Content-Type,X-Request-With,Ajax-Request');
        $this->response->status(200);
        // $response
        return $this->response->end('abcdefg');
    }
}