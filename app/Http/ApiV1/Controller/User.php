<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2020/4/21
 * Time: 11:42
 */

namespace App\Http\ApiV1\Controller;


use App\Http\ApiV1\Action\UserAction;

class User extends Auth
{
    public function editUserInfo()
    {
        $param = $this->request->post;
        $param['nickname'] = $param['nickname'] ?? '';
        $param['avatar'] = $param['avatar'] ?? '';
        $res = UserAction::editUserInfo($this , $param);
        if ($res['code'] != 0) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }
}