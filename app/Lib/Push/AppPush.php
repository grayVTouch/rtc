<?php

namespace App\Lib\Push;


/**
 * 极光推送
 * 
 * app 推送
 *
 * 操作步骤：
 *
 * 1. 绑定用户标识 和 设备id
 * 2. 提供用户 id 向置顶用户推送
 *
 * 解绑：
 *
 * 这边操作比较特殊，当某个用户id 绑定某台设备之后，那么该用户id之前绑定的设备将自动解绑
 */

use Core\Lib\Http;

class AppPush {

    private const SUCCESS_CODE = 0;

    private static $api = 'http://push.t1.tuuz.cc';

    private static $token = 'nimo';

    /*
     *
     * 上面的token为API系统的标识符，用于区分项目
     *
     *
     * user_sync是用户同步模块，应该在用户每次打开APP的时候调用本功能接口uid_or_username的意义为如果你的系统使用的用户唯一的标识符为user表自增id，那么uid_or_username就填写你系统中该用户的user_id即可，rid为设备识别码id由前端提供，详情可以参考极光registration_id说明，必须要先使用本接口同步用户信息才可推送，你方系统中可以不需要对rid进行保留处理，下方接口会说明
     *
     *
     *
     * 可是使用 single 方法做单人推送，使用 push_more 方法做多人推送，使用push_all方法做全员推送
     *
     * push_single/push_more方法中，uid_or_username为你在sync接口中传入的用户标识数据（例如uid或者username），content为内容主体，苹果是没有title的，所以在苹果上content就是title，安卓上有title（部分系统没有），所以content意义大于title，请不要设定过长content避免推送后因为用户手机顶栏长度有限而无法完全显示你希望推送消息的全文
     * 在more方法中，为uids_or_users，这里因该传入array信息例如[20,21,56,43]这样的uid数据即可同时推送给这部分人
     * 如果需要特殊推送，可以加入extra消息，注意extra一定是一个object类型的数据，不能是array否则推送将会直接报错无法执行，另外extra是额外消息，本消息只能由apicloud通过eventlisterner接收，随推顶栏推送接收，一般用于跳转落地页，有需要请自行设定
     *
     * push_all方法为全员推送，无定向性，所有人都会收到，应该避免线上测试误操作调用本接口
     *
     *
     *
     */

    // 顶栏推送/单人推送
    public static function push(int $user_id , string $content, string $title = '' , $extra = null)
    {
        return self::pushAll([$user_id] , $content , $title , $extra);
    }

    // 顶栏推送/多人推送
    public static function pushAll(array $user_ids , $content , string $title = '' , $extra = null)
    {
        $data = [];
        $data['users'] = json_encode($user_ids);
        $data['content'] = $content;
        $data['title'] = $title;
//        $data['extra'] = $extra;
        $data['extra'] = [1 => 1];
        $res = self::curl('/push' , $data);
        if (empty($res)) {
            return self::response('请求发送失败，请检查网络' , 500);
        }
        $res = json_decode($res , true);
        if ($res['code'] != self::SUCCESS_CODE) {
            return self::response(self::errorMessage(__FILE__ , __LINE__ , $res) , 500);
        }
        return self::response($res['data']);
    }

    // 静默推送/单人推送
    public static function pushWithQuiet($uid, $content, $extra) {
        return self::pushAllWithQuiet([$uid] , $content , $extra);
    }

    // 静默推送/多人推送
    public static function pushAllWithQuiet(array $user_ids , $content , $extra) {
        $data = [];
        $data['users']      = json_encode($user_ids);
        $data['content']    = $content;
        $data['extra']      = $extra;
        $res = self::curl('/message' , $data);
        if (empty($res)) {
            return self::response('请求发送失败，请检查网络' , 500);
        }
        $res = json_decode($res , true);
        if ($res['code'] != self::SUCCESS_CODE) {
            return self::response(self::errorMessage(__FILE__ , __LINE__ , $res) , 500);
        }
        return self::response($res['data']);
    }

    // 全推
    public static function fullPush($content, $title = null, $extra = null) {
        $data = [];
        $data['content'] = $content;
        $data['title'] = $title;
        $data['extra'] = $extra;
        $res = self::curl('/push_all' , $data);
        if (empty($res)) {
            return self::response('请求发送失败，请检查网络' , 500);
        }
        $res = json_decode($res , true);
        if ($res['code'] != self::SUCCESS_CODE) {
            return self::response(self::errorMessage(__FILE__ , __LINE__ , $res) , 500);
        }
        return self::response($res['data']);
    }

    /**
     * 用户同步（uid 绑定 设备id）
     *
     * @param $user_id 用户id
     * @param $registration_id 设备id
     */
    public static function sync($user_id, $registration_id) {
        $data = [];
        $data['user']   = $user_id;
        $data['rid']    = $registration_id;
        $res = self::curl('/sync' , $data);
        if (empty($res)) {
            return self::response('请求发送失败，请检查网络' , 500);
        }
        $res = json_decode($res , true);
        if ($res['code'] != self::SUCCESS_CODE) {
            return self::response(self::errorMessage(__FILE__ , __LINE__ , $res) , 500);
        }
        return self::response($res['data']);
    }

    private static function errorMessage($filename , $line , array $data)
    {
        return sprintf('Error! File: %s; Line: %s; Message: %s' , $filename , $line , json_encode($data));
    }

    private static function response($data = '' , int $code = 200)
    {
        return [
            'code' => $code ,
            'data' => $data
        ];
    }

    // 推送
    private static function curl(string $path = '/sync' , array $data = []) {
        if (empty($data)) {
            return false;
        }
        $data['token'] = self::$token;
        $path = rtrim($path , '/');
        $url = sprintf('%s/%s' , self::$api , $path);

        var_dump('极光推送数据：' . $url . '调试开始------------');
        print_r([
            'data' => $data ,
        ]);
        var_dump('极光推送数据....调试结束-----------');

        return Http::post($url , [
            'data' => $data ,
        ]);
    }

}
