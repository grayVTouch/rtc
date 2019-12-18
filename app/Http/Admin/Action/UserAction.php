<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/2
 * Time: 16:13
 */

namespace App\Http\Admin\Action;


use App\Http\Admin\Auth;
use App\Model\UserModel;
use App\Util\UserUtil;
use Core\Lib\Validator;
use Exception;
use Illuminate\Support\Facades\DB;

class UserAction extends Action
{
    public static function del(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'unique_code' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $user = UserModel::findByUniqueCode($param['unique_code']);
        if (empty($user)) {
            return self::error('用户不存在' , 404);
        }
        try {
            DB::beginTransaction();
            UserUtil::delete($user->identifier , $user->id);
            DB::commit();
            return self::success();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}