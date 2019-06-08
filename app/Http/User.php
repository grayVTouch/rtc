<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/5
 * Time: 22:10
 */

namespace App\Http;


use App\Http\Action\UserAction;

class User extends Auth
{
    // 修改用户头像
    public function edit()
    {
        $param = $this->request->post;
        $param['nickname'] = $param['nickname'] ?? '';
        $param['avatar'] = $param['avatar'] ?? '';
        $param['role'] = $param['role'] ?? '';
        $res = UserAction::edit($this , $param);
        if ($res['code'] != 200) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }
}