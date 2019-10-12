<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace SMS;

/**
 * Description of Zz253
 *
 * @author Sammy Guergachi <sguergachi at gmail.com>
 */
class Zz253 {

	public static $key = '';
	public static $secret = '';

	public static function send($quhao, $phone, $code) {
		if ($quhao == '+86') {
			$arr = [
				"account" => 'N523530_N5402271',
				"password" => 'puSsjGlFQIb8b7',
				"msg" => "您的动态验证码为" . $code . "请在页面输入完成验证。如非本人操作请忽略。 ",
				"phone" => $phone,
			];
			$arr = json_encode($arr);
			$header  = [
				'Content-Type: application/json; charset=utf-8',
				'Content-Length: ' . strlen($arr)
			];
			$ret = curl('http://smssh1.253.com/msg/send/json', $arr,'post',$header);
			$ret = json_decode($ret , true);
			if ($ret['code'] == 0) {
				return ['code'=>0,'msg'=>'success'];
			} else {
				return ['code'=>1,'msg'=>$ret['errorMsg']];
			}
		} else {
			$clapi  = new \Z253\ChuanglanSmsApi();
			$result = $clapi->sendInternational($quhao.$phone, "您的动态验证码为" . $code . "请在页面输入完成验证。如非本人操作请忽略。 ");

			if(!is_null(json_decode($result))){
				
				$output=json_decode($result,true);
				if(isset($output['code'])  && $output['code']=='0'){
					return ['code'=>0,'msg'=>'success'];
				}else{
					return ['code'=>1,'msg'=>$output['error']];
				}
			}else{
				return ['code'=>1,'msg'=>'发送失败'];
			}
		}
	}
}
