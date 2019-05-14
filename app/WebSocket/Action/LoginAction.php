<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/12
 * Time: 9:39
 */

namespace App\WebSocket\Action;


use App\Model\User;
use App\Model\UserToken;
use function core\array_unit;
use Core\Lib\Validator;
use function core\ssl_random;


class LoginAction extends Action
{
    public static function login(array $param)
    {
        $validator = Validator::make($param , [
            'unique_code'    => 'required' ,
        ] , [
            'unique_code.required' => '必须' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->error());
        }
        $user = User::findByUniqueCode($param['unique_code']);
        if (empty($user)) {
            return self::error([
                'unique_code' => '未找到当前提供的 unique_code 对应的用户' ,
            ]);
        }
        // 登录成功
        $param['identifier'] = $user->identifier;
        $param['user_id'] = $user->user_id;
        $param['token']  = ssl_random(256);
        $param['expire'] = date('Y-m-d H:i:s' , time() + config('app.timeout'));
        UserToken::insert(array_unit($param , [
            'token' ,
            'expire' ,
            'user_id' ,
            'identifier' ,
        ]));
        return self::success($param['token']);
    }
}