<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/10/31
 * Time: 15:28
 */

namespace App\WebSocket;


use App\WebSocket\Action\ArticleNoAuthAction;

class Article extends Base
{
    // 用户协议
    protected $articleTypeIdForUserProtocol = 6;

    // 用户协议
    public function userProtocol(array $param)
    {
        $res = ArticleNoAuthAction::firstByArticleTypeId($this , $this->articleTypeIdForUserProtocol , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }
}