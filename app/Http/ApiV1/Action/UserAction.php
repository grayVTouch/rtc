<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2020/4/21
 * Time: 11:42
 */

namespace App\Http\ApiV1\Action;


use App\Http\ApiV1\Controller\Auth;
use App\Http\ApiV1\Data\UserData;
use function core\array_unit;

class UserAction extends Action
{
    // 仅给他修改用户信息的接口
    public static function editUserInfo(Auth $auth , array $param)
    {
        $param['avatar'] = $param['avatar'] === '' ? $auth->user->avatar : $param['avatar'];
        $param['sex'] = $param['sex'] === '' ? $auth->user->sex : $param['sex'];
        $param['birthday'] = $param['birthday'] === '' ? $auth->user->birthday : $param['birthday'];
        $param['nickname'] = $param['nickname'] === '' ? $auth->user->nickname : $param['nickname'];
        $param['signature'] = $param['signature'] === '' ? $auth->user->signature : $param['signature'];
        UserData::updateByIdentifierAndIdAndData($auth->identifier , $auth->user->id , array_unit($param , [
            'avatar' ,
            'sex' ,
            'birthday' ,
            'nickname' ,
            'signature' ,
        ]));
        return self::success();
    }
}