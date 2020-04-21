<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2020/4/16
 * Time: 9:52
 */

namespace App\Http\ApiV1\Action;


use App\Http\WebV1\Controller\Base;
use Core\Lib\Validator;

class PushNoAuthAction extends Action
{
    public static function notifyAll(Base $base , array $param)
    {
        $validator = Validator::make($param , [
            'user_id' => 'required' ,
            'type' => 'required'
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $user_id = json_decode($param['user_id'] , true);
        if (empty($user_id)) {
            return self::error('请提供要推送的用户');
        }
        $data = json_decode($param['data'] , true);
        if (is_null($data)) {
            $data = $param['data'];
        }
        // 群推
        $base->pushAll($user_id , $param['type'] , $data);
        return self::success();
    }

    public static function notify(Base $base , array $param)
    {
        $validator = Validator::make($param , [
            'user_id' => 'required' ,
            'type' => 'required'
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $data = json_decode($param['data'] , true);
        if (is_null($data)) {
            $data = $param['data'];
        }
        // 群推
        $base->push($param['user_id'] , $param['type'] , $data);
        return self::success();
    }
}