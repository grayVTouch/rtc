<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2020/4/20
 * Time: 15:15
 */

namespace App\WebSocket\V1\Util;


use function core\random;

class OrderUtil extends Util
{
    // 生成订单号
    public static function orderNo()
    {
        $order_no = date('ymdhis' , time());
        $random = random(6 , 'number' , true);
        return $order_no . $random;
    }
}