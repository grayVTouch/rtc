<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/19
 * Time: 9:50
 */

namespace App\Http\Admin\Action;

use App\Model\JoinFriendMethodModel;
use App\Model\UserJoinFriendOptionModel;
use App\Model\UserModel;
use App\Model\UserOptionModel;
use App\Util\MiscUtil;
use function core\array_unit;
use Core\Lib\Validator;
use Exception;
use Illuminate\Support\Facades\DB;

class LoginAction extends Action
{
    public static function register(array $param)
    {
        $validator = Validator::make($param , [
            'role' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $admin_role_range = ['admin' , 'super_admin'];
        if (!in_array($param['role'] , $admin_role_range)) {
            return self::error('不支持的角色类型，当前支持的有：' . implode(',' , $admin_role_range));
        }
        $unique_code = MiscUtil::uniqueCode();
        $param['unique_code'] = $unique_code;
        try {
            DB::beginTransaction();
            $id = UserModel::insertGetId(array_unit($param , [
                'role' ,
                'unique_code' ,
            ]));
            UserOptionModel::insertGetId([
                'user_id' => $id ,
            ]);
            // 新增用户添加方式选项
            $join_friend_method = JoinFriendMethodModel::getAll();
            foreach ($join_friend_method as $v)
            {
                UserJoinFriendOptionModel::insertGetId([
                    'join_friend_method_id' => $v->id ,
                    'user_id' => $id ,
                    'enable' => 1 ,
                ]);
            }
            DB::commit();
            return self::success($unique_code);
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}