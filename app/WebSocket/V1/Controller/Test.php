<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2020/2/14
 * Time: 11:20
 */

namespace App\WebSocket\V1\Controller;


use App\WebSocket\V1\Util\BaiDuTranslationUtil;

class Test extends Base
{
    public function index(array $param)
    {
        $res = BaiDuTranslationUtil::translate('my name is grayvtouch' , 'auto' , 'jp');
        var_dump("翻译结果：" . $res);
    }
}