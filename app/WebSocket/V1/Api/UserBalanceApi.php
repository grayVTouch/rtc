<?php

namespace App\WebSocket\V1\Api;

/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2020/4/20
 * Time: 14:01
 */

class UserBalanceApi extends Api
{

    // 获取用户余额列表
    public static function getBalance($user_id)
    {
        $path = '/api/auth/hb_balance';
        $api = self::genApiByPathInShop($path);
        return self::post($api , [
            'user_id' => $user_id
        ]);
    }

    /**
     * 更新用户余额
     *
     * @param $order_no 订单号
     * @param $user_id 用户Id
     * @param $coin_id 币种id
     * @param $type 类型 send-发送红包 | receive-领取红包 | refund-退款
     * @param $money 变更金额 正数（增加） | 负数（减少）
     */
    public static function updateBalance($order_no , $user_id , $coin_id , $money , $type , $desc = '' , $password = '')
    {
        $path = '/api/auth/hb';
        $api = self::genApiByPathInShop($path);

        print_r([
            'order_no'  => $order_no ,
            'user_id'   => $user_id ,
            'type'      => $type ,
            'coin_id'   => $coin_id ,
            'money'     => $money ,
            'desc'      => $desc ,
            'password'  => $password ,
        ]);
        return self::post($api , [
            'order_no'  => $order_no ,
            'user_id'   => $user_id ,
            'type'      => $type ,
            'coin_id'   => $coin_id ,
            'money'     => $money ,
            'desc'      => $desc ,
            'password'  => $password ,
        ]);
    }

    // 获取币种列表
    public static function myCoin($user_id)
    {
        $path = '/api/auth/hb_coin';
        $api = self::genApiByPathInShop($path);
        return self::post($api , [
            'user_id' => $user_id ,
        ]);
    }
}