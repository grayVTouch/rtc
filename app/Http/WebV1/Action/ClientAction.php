<?php
/**
 *
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2020/3/13
 * Time: 10:42
 */

namespace App\Http\WebV1\Action;

use App\Http\WebV1\Controller\Base;
use App\Http\WebV1\Util\PushUtil;
use Core\Lib\Validator;

class ClientAction extends Action
{
    public static function push(Base $base , array $param)
    {
        $validator = Validator::make($param , [
            'client' => 'required' ,
            'data'   => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $client = json_decode($param['client'] , true);
        if (empty($client)) {
            return self::error('请提供客户端 id');
        }
        $data = json_decode($param['data'] , true);
        if (empty($data)) {
            return self::error('请提供要发送的数据');
        }
        $exclude = json_decode($param['exclude'] , true);
        $exclude = empty($exclude) ? [] : $exclude;
        $res = PushUtil::multipleForClient($base->identifier , $client , $data['type'] , $data['data'] , $exclude);
        return self::success($res);
    }
}