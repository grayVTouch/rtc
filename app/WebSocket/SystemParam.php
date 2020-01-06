<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2020/1/6
 * Time: 15:51
 */

namespace App\WebSocket;


use App\WebSocket\Action\SystemParamAction;

class SystemParam extends Base
{
    public function pcDomain(array $param)
    {
        $res = SystemParamAction::pcDomain($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    public function enablePc(array $param)
    {
        $res = SystemParamAction::enablePc($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    public function appName(array $param)
    {
        $res = SystemParamAction::appName($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }
}