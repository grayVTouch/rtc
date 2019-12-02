<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/19
 * Time: 10:23
 */

namespace App\Http;


class Index extends Base
{
    public function index()
    {
        return $this->success('欢迎使用 grayVTouch 开发的及时通迅系统 Http 引擎');
    }
}