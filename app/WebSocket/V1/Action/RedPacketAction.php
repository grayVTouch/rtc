<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2020/2/12
 * Time: 11:49
 */

namespace App\WebSocket\V1\Action;


use App\WebSocket\V1\Controller\Auth;
use App\WebSocket\V1\Data\UserData;
use App\WebSocket\V1\Model\FundLogModel;
use App\WebSocket\V1\Model\RedPacketModel;
use App\WebSocket\V1\Model\RedPacketReceiveLogModel;
use App\WebSocket\V1\Model\UserModel;
use App\WebSocket\V1\Util\ChatUtil;
use App\WebSocket\V1\Util\UserUtil;
use Core\Lib\Hash;
use Core\Lib\Validator;
use Exception;
use Illuminate\Support\Facades\DB;

class RedPacketAction extends Action
{
    public static function createRedPacketForPrivate(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'other_id'      => 'required' ,
            'pay_password'  => 'required' ,
            'money'         => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        // 检查用户密码是否正确
        if (!Hash::check($param['pay_password'] , $auth->user->pay_password)) {
            return self::error('支付密码错误');
        }
        // 用户是否存在
        $other = UserModel::findById($param['other_id']);
        if (empty($other)) {
            return self::error('user_id：' . $param['other_id'] . '为找到' , 404);
        }
        try {
            DB::beginTransaction();
            $balance = UserModel::getBalanceByUserIdWithLock($auth->user->id);
            $cur_balance = bcsub($balance , $param['money']);
            if ($cur_balance < 0) {
                DB::rollBack();
                return self::error('当前余额不够' , 403);
            }
            $red_packet_id = RedPacketModel::insertGetId([
                'user_id' => $auth->user->id ,
                'identifier' => $auth->identifier ,
                'type' => 'private' ,
                // 私聊红包默认都是 普通红包
                'sub_type' => 'common' ,
                'money' => $param['money'] ,
                'number' => 1 ,
                'receiver' => $param['other_id'] ,
            ]);
            UserData::updateByIdentifierAndIdAndData($auth->identifier , $auth->user->id , [
                'balance' => $cur_balance
            ]);
            FundLogModel::insertGetId([
                'user_id' => $auth->user->id ,
                'identifier' => $auth->identifier ,
                'type' => 'red_packet' ,
                'before' => $balance ,
                'after' => $cur_balance ,
                'desc' => '发送红包' ,
            ]);
            // 发送红包消息
            $res = ChatUtil::send($auth , [
                'user_id'   => $auth->user->id ,
                'other_id'  => $other->id ,
                'type'      => 'red_packet' ,
                'message'   => $red_packet_id ,
                'old'       => 1 ,
            ] , true);
            if ($res['code'] != 0) {
                DB::rollBack();
                return self::error('创建红包失败（发送红包消息失败: ' . $res['data'] . '）');
            }
            $msg = $res['data'];
            RedPacketModel::updateById($red_packet_id , [
                'message_id' => $msg->id ,
            ]);
            DB::commit();
            return self::success();
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function receiveRedPacketForPrivate(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'red_packet_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        try {
            DB::beginTransaction();
            $red_packet = RedPacketModel::findByIdWithLock($param['red_packet_id']);
            if (empty($red_packet)) {
                DB::rollBack();
                return self::error('未找到红包信息' , 404);
            }
            if ($red_packet->is_expired) {
                DB::rollBack();
                return self::error('红包已经过期' , 403);
            }
            if ($red_packet->is_received) {
                DB::rollBack();
                return self::error('红包已经被领取完' , 403);
            }
            if ($red_packet->user_id == $auth->user->id) {
                DB::rollBack();
                return self::error('禁止领取自己发送的红包' , 403);
            }
            if ($red_packet->receiver != $auth->user->id) {
                DB::rollBack();
                return self::error('非法操作' , 403);
            }
            $balance = UserModel::getBalanceByUserIdWithLock($auth->user->id);
            $cur_balance = bcadd($balance , $red_packet->money);
            RedPacketModel::updateById($red_packet->id , [
                'is_received' => 1 ,
                'received_number' => 1 ,
                'received_money' => $red_packet->money
            ]);
            RedPacketReceiveLogModel::insertGetId([
                'user_id' => $auth->user->id ,
                'identifier' => $auth->identifier ,
                'red_packet_id' => $red_packet->id ,
                'money' => $red_packet->money ,
            ]);
            FundLogModel::insertGetId([
                'user_id' => $auth->user->id ,
                'identifier' => $auth->identifier ,
                'type' => 'red_packet' ,
                'before' => $balance ,
                'after' => $cur_balance ,
                'desc' => '领取红包' ,
            ]);
            UserData::updateByIdentifierAndIdAndData($auth->identifier , $auth->user->id , [
                'balance' => $cur_balance
            ]);
            $res = ChatUtil::send($auth , [
                'user_id' => $auth->user->id ,
                'other_id' => $red_packet->user_id ,
                'type' => 'notification' ,
                'message' => sprintf('%s 领取了红包' , UserUtil::getNameFromNicknameAndUsername($auth->user->nickname , $auth->user->username)) ,
                'old' => 1 ,
            ] , true);
            if ($res['code'] != 0) {
                DB::rollBack();
                return self::error('领取红包失败（发送消息失败：' . $res['data'] . '）');
            }
            DB::commit();
            return self::success();
        } catch(Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    public static function createRedPacketForGroup(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'group_id'      => 'required' ,
            'pay_password'  => 'required' ,
            'money'         => 'required' ,
            'type'          => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        // 检查用户密码是否正确
        if (!Hash::check($param['pay_password'] , $auth->user->pay_password)) {
            return self::error('支付密码错误');
        }
        $red_packet_type = config('business.red_packet_type');
        if (!in_array($param['type'] , $red_packet_type)) {
            return self::error('不支持的红包类型，当前支持的红包类型有：' . implode($red_packet_type));
        }
        try {
            DB::beginTransaction();
            $balance = UserModel::getBalanceByUserIdWithLock($auth->user->id);
            $cur_balance = bcsub($balance , $param['money']);
            if ($cur_balance < 0) {
                DB::rollBack();
                return self::error('当前余额不够' , 403);
            }
            RedPacketModel::insertGetId([
                'user_id' => $auth->user->id ,
                'identifier' => $auth->identifier ,
                'type' => 'private' ,
                'money' => $param['money'] ,
                'number' => 1 ,
                'receiver' => $param['other_id'] ,
            ]);
            UserData::updateByIdentifierAndIdAndData($auth->identifier , $auth->user->id , [
                'balance' => $cur_balance
            ]);
            FundLogModel::insertGetId([
                'user_id' => $auth->user->id ,
                'identifier' => $auth->identifier ,
                'type' => 'red_packet' ,
                'before' => $balance ,
                'after' => $cur_balance ,
                'desc' => '发送红包' ,
            ]);
            DB::commit();
            return self::success();
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}