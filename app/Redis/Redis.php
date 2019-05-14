<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/11
 * Time: 17:43
 */
namespace App\Redis;

class Redis
{
    // 命名规则 大类别 + 项目Id + id
    protected static $fdKey = 'fd_%s_%s';
}