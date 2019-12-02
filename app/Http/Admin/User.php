<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/2
 * Time: 16:13
 */

namespace App\Http\Admin;


use App\Http\Admin\Action\UserAction;

class User extends Auth
{
    // 删除用户
    public function del()
    {
        $param = $this->request->post;
        $param['unique_code'] = $param['unique_code'] ?? '';
        $res = UserAction::del($this , $param);
        if ($res['code'] != 0) {
            return $this->error($res['data'] , $res['code']);
        }
        return $this->success($res['data']);
    }
}