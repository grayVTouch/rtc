<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2020/4/21
 * Time: 10:28
 */

namespace App\Http\ApiV1\Action;


use App\Http\ApiV1\Controller\Base;
use App\Http\ApiV1\Model\FriendModel;
use App\Http\ApiV1\Model\JoinFriendMethodModel;
use App\Http\ApiV1\Model\PushModel;
use App\Http\ApiV1\Model\PushReadStatusModel;
use App\Http\ApiV1\Model\UserJoinFriendOptionModel;
use App\Http\ApiV1\Model\UserModel;
use App\Http\ApiV1\Model\UserOptionModel;
use App\Http\ApiV1\Model\UserTokenModel;
use App\Http\ApiV1\Util\MiscUtil;
use App\Http\ApiV1\Util\SessionUtil;
use function core\array_unit;
use Core\Lib\Validator;
use Exception;
use Illuminate\Support\Facades\DB;

class LoginAction extends Action
{
    /**
     * 第三方登录使用 unique_code
     */
    public static function login(Base $base , array $param)
    {
        $validator = Validator::make($param , [
            'unique_code' => 'required' ,
            'token'       => 'required' ,
            'platform'      => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $platform = config('business.platform');
        if (!in_array($param['platform'] , $platform)) {
            return self::error('不支持的平台，当前受支持的平台有：' . implode(',' , $platform));
        }
        $user = UserModel::findByUniqueCode($param['unique_code']);
        if (empty($user)) {
            return self::error('用户不存在' , 404);
        }
        $expire = date('Y-m-d H:i:s' , time() + config('app.timeout'));
        try {
            // 将同平台的其他登录客户端的凭证删除
            UserTokenModel::delByUserIdAndPlatform($user->id , $param['platform']);
            UserTokenModel::u_insertGetId($base->identifier , $user->id , $param['token'] , $expire , $param['platform']);
            return self::success();
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 注册
     */
    public static function register(Base $base , array $param)
    {
//        $validator = Validator::make($param , [
//
//        ]);
//        if ($validator->fails()) {
//            return self::error($validator->message());
//        }
        $param['identifier'] = $base->identifier;
        $param['unique_code'] = MiscUtil::uniqueCode();
        $param['aes_key']   = MiscUtil::aesKey();
        try {
            DB::beginTransaction();
            $id = UserModel::insertGetId(array_unit($param , [
                'unique_code' ,
                'identifier' ,
                'nickname' ,
                'avatar' ,
                'aes_key' ,
            ]));
            UserOptionModel::insertGetId([
                'user_id' => $id ,
            ]);
            // 自动添加客服为好友（这边默认每个项目仅会有一个客服）
            $system_user = UserModel::systemUser($base->identifier);
            FriendModel::u_insertGetId($base->identifier , $id , $system_user->id);
            FriendModel::u_insertGetId($base->identifier , $system_user->id , $id);
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
            // 新用户注册推送
            $new_user_notification = config('app.new_user_notification');
            $system = 'system';
            $push_id = PushModel::u_insertGetId($base->identifier , 'single' , $system , '' , $id , $new_user_notification , $new_user_notification , $new_user_notification , 0);
            PushReadStatusModel::u_insertGetId($base->identifier , $id , $push_id , $system , 0);
            SessionUtil::createOrUpdate($base->identifier , $id , $system , '');
            DB::commit();
            return self::success($param['unique_code']);
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}