<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2020/1/6
 * Time: 15:51
 */

namespace App\WebSocket\V1\Action;


use App\WebSocket\V1\Model\SystemParamModel;
use App\WebSocket\V1\Controller\Base;

class SystemParamAction extends Action
{
    public static function pcDomain(Base $base , array $param)
    {
        $pc_domain = SystemParamModel::getValueByKey('pc_domain');
        return self::success($pc_domain);
    }

    public static function enablePc(Base $base , array $param)
    {
        $enable_pc = SystemParamModel::getValueByKey('enable_pc');
        return self::success($enable_pc);
    }

    public static function appName(Base $base , array $param)
    {
        $name = SystemParamModel::getValueByKey('app_name');
        return self::success($name);
    }
}