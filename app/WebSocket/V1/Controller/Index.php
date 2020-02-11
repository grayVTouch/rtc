<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/11
 * Time: 18:52
 */

namespace App\WebSocket\V1\Controller;


class Index extends Base
{
    public function index()
    {
        $this->success('欢迎访问 grayVTouch 开发的 WebSocket 引擎');
    }
}