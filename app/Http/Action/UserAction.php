<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/5
 * Time: 22:12
 */

namespace App\Http\Action;


use App\Http\Auth;
use App\Model\UserModel;
use App\Model\UserInfoModel;
use Exception;
use function core\array_unit;
use Illuminate\Support\Facades\DB;

class UserAction extends Action
{
    // 编辑
    public static function edit(Auth $auth , array $param)
    {
        $param['nickname'] = $param['nickname'] ? $param['nickname'] : $auth->user->nickname;
        $param['avatar'] = $param['avatar'] ? $param['avatar'] : $auth->user->avatar;
        $param['role'] = $param['role'] ? $param['role'] : $auth->user->role;
        try {
            DB::beginTransaction();
            UserModel::updateById($auth->user->id , array_unit($param , [
                'role' ,
            ]));
            UserInfoModel::updateById($auth->user->id , array_unit($param , [
                'nickname' ,
                'avatar'
            ]));
            DB::commit();
            return self::success();
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}