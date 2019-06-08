<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/5
 * Time: 22:12
 */

namespace App\Http\Action;


use App\Http\Auth;
use App\Model\User;
use function extra\array_unit;

class UserAction extends Action
{
    // 编辑
    public static function edit(Auth $auth , array $param)
    {
        $param['nickname'] = $param['nickname'] ? $param['nickname'] : $auth->user->nickname;
        $param['avatar'] = $param['avatar'] ? $param['avatar'] : $auth->user->avatar;
        $param['role'] = $param['role'] ? $param['role'] : $auth->user->role;
        $res = User::updateById($auth->user->id , array_unit($param , [
            'nickname' ,
            'avatar' ,
            'role' ,
        ]));
        return self::success($res);
    }
}