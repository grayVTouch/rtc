<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2020/2/12
 * Time: 11:49
 */

namespace App\WebSocket\V1\Action;


use App\WebSocket\V1\Controller\Auth;
use App\WebSocket\V1\Data\GroupData;
use App\WebSocket\V1\Data\GroupMemberData;
use App\WebSocket\V1\Data\RedPacketData;
use App\WebSocket\V1\Data\UserData;
use App\WebSocket\V1\Model\FundLogModel;
use App\WebSocket\V1\Model\GroupMemberModel;
use App\WebSocket\V1\Model\RedPacketModel;
use App\WebSocket\V1\Model\RedPacketReceiveLogModel;
use App\WebSocket\V1\Model\UserModel;
use App\WebSocket\V1\Redis\RedPacketReceivedLogRedis;
use App\WebSocket\V1\Redis\RedPacketRedis;
use App\WebSocket\V1\Util\ChatUtil;
use App\WebSocket\V1\Util\RedPacketReceiveLogUtil;
use App\WebSocket\V1\Util\UserUtil;
use function core\decimal_random;
use Core\Lib\Hash;
use Core\Lib\Validator;
use Exception;

use Illuminate\Support\Facades\DB;

class RedPacketAction extends Action
{
    public static function createRedPacketForPrivate(Auth $auth , array $param)
    {
//        $s_time = microtime(true);
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
        $param['remark'] = empty($param['remark']) ? config('app.red_packet_remark') : $param['remark'];
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
                'remark' => $param['remark'] ,
            ]);
            UserData::updateByIdentifierAndIdAndData($auth->identifier , $auth->user->id , [
                'balance' => $cur_balance
            ]);
            $decimal_digit = config('app.decimal_digit');
            FundLogModel::insertGetId([
                'user_id' => $auth->user->id ,
                'identifier' => $auth->identifier ,
                'type' => 'red_packet' ,
                'before' => $balance ,
                'after' => $cur_balance ,
                'money' => bcsub($cur_balance , $balance , $decimal_digit) ,
                'desc' => '发送红包' ,
            ]);
            // 发送红包消息
            $res = ChatUtil::sendForRedPacketStep1($auth , [
                'user_id'   => $auth->user->id ,
                'other_id'  => $other->id ,
                'type'      => 'red_packet' ,
                'message'   => $red_packet_id ,
                'old'       => 1 ,
            ]);
            if ($res['code'] != 0) {
                DB::rollBack();
                return self::error('创建红包失败（发送红包消息失败: ' . $res['data'] . '）');
            }
            $msg = $res['data'];
            RedPacketModel::updateById($red_packet_id , [
                'message_id' => $msg->id ,
            ]);
            DB::commit();
            ChatUtil::sendForRedPacketStep2($auth , $msg);
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
            $time = time();
            $red_packet_expired_duration = config('app.red_packet_expired_duration');
            if (strtotime($red_packet->create_time) + $red_packet_expired_duration < $time) {
                RedPacketModel::updateById($red_packet->id , [
                    'is_expired' => 1
                ]);
                DB::commit();
                return self::error('红包已经过期');
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
                'received_money' => $red_packet->money ,
                'received_time' => date('Y-m-d H:i:s')
            ]);
            RedPacketReceiveLogModel::insertGetId([
                'user_id' => $auth->user->id ,
                'identifier' => $auth->identifier ,
                'red_packet_id' => $red_packet->id ,
                'money' => $red_packet->money ,
            ]);
            $decimal_digit = config('app.decimal_digit');
            FundLogModel::insertGetId([
                'user_id' => $auth->user->id ,
                'identifier' => $auth->identifier ,
                'type' => 'red_packet' ,
                'before' => $balance ,
                'after' => $cur_balance ,
                'money' => bcsub($cur_balance , $balance , $decimal_digit) ,
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
            'number'        => 'required' ,
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
        // 检查群是否存在
        $group = GroupData::findByIdentifierAndId($auth->identifier , $param['group_id']);
        if (empty($group)) {
            return self::error('群不存在' , 404);
        }
        $member = GroupMemberData::findByIdentifierAndGroupIdAndUserId($auth->identifier , $group->id , $auth->user->id);
        if (empty($member)) {
            return self::error('您并非该群成员' , 403);
        }
        // 红包的数量限制
        $group_member_count = GroupMemberModel::countByGroupId($group->id);
        if ($param['number'] < 1 || $param['number'] > $group_member_count) {
            return self::error('红包数量要求 >= 1 且 <= 群成员数量[' . $group_member_count . ']');
        }
        $param['remark'] = empty($param['remark']) ? config('app.red_packet_remark') : $param['remark'];
        $red_packet_id = 0;
        try {
            DB::beginTransaction();
            $balance = UserModel::getBalanceByUserIdWithLock($auth->user->id);
            $cur_balance = bcsub($balance , $param['money']);
            if ($cur_balance < 0) {
                DB::rollBack();
                return self::error('当前余额不够' , 403);
            }
            // 红包金额保留的小数位数
            $decimal_digit = config('app.decimal_digit');
            $moneys = [];
            // 分配红包金额
            switch ($param['type'])
            {
                case 'common':
                    // 普通红包（每个人领取的金额是相同的）
                    $unit = bcdiv($param['money'] , $param['number'] , $decimal_digit);
                    $moneys = array_pad([] , $param['number'] , $unit);
                    break;
                case 'random':
                    $moneys = decimal_random($param['money'] , $param['number'] , $decimal_digit);
                    if ($moneys === false) {
                        DB::rollBack();
                        return self::error('随机生成给定数量的领取金额失败，请确认红包金额，红包数量是否合理，然后重试！');
                    }
                    break;
            }
            $red_packet_id = RedPacketModel::insertGetId([
                'user_id' => $auth->user->id ,
                'identifier' => $auth->identifier ,
                'type' => 'group' ,
                'sub_type' => $param['type'] ,
                'money' => $param['money'] ,
                'number' => $param['number'] ,
                'group_id' => $group->id ,
                'remark' => $param['remark'] ,
            ]);
            // 保存到 redis
            RedPacketRedis::redPacketByIdentifierAndRedPacketIdAndList($auth->identifier , $red_packet_id , $moneys);
            UserData::updateByIdentifierAndIdAndData($auth->identifier , $auth->user->id , [
                'balance' => $cur_balance
            ]);
            FundLogModel::insertGetId([
                'user_id' => $auth->user->id ,
                'identifier' => $auth->identifier ,
                'type' => 'red_packet' ,
                'before' => $balance ,
                'after' => $cur_balance ,
                'money' => bcsub($cur_balance , $balance , $decimal_digit) ,
                'desc' => '发送红包' ,
            ]);
            $res = ChatUtil::groupSendForRedPacketStep1($auth , $auth->user->id , [
                'user_id' => $auth->user->id ,
                'group_id' => $group->id ,
                'type' => 'red_packet' ,
                'message' => $red_packet_id ,
                'old' => 1 ,
            ]);
            if ($res['code'] != 0) {
                RedPacketRedis::delByIdentifierAndRedPacketId($auth->identifier , $red_packet_id);
                DB::rollBack();
                return self::error('创建红包失败（发送红包消息失败：' . $res['data'] . '）');
            }
            $res = $res['data'];
            // 更新红包消息对应的消息id
            RedPacketModel::updateById($red_packet_id , [
                'message_id' => $res['message']->id
            ]);
            DB::commit();
            ChatUtil::groupSendForRedPacketStep2($auth , $res['user_ids'] , $res['message']);
            return self::success();
        } catch(Exception $e) {
            RedPacketRedis::delByIdentifierAndRedPacketId($auth->identifier , $red_packet_id);
            DB::rollBack();
            throw $e;
        }
    }

    public static function receiveRedPacketForGroup(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'red_packet_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        // 检查是否已经领取过该红包
        $red_packet_received_log = RedPacketReceivedLogRedis::redPacketReceivedLogByIdentifierAndRedPacketIdAndUserIdAndVal($auth->identifier , $param['red_packet_id'] , $auth->user->id);
        if ($red_packet_received_log !== false) {
            return self::error('你已经领取过该红包' , 403);
        }
        // 新增领取记录
        RedPacketReceivedLogRedis::redPacketReceivedLogByIdentifierAndRedPacketIdAndUserIdAndVal($auth->identifier , $param['red_packet_id'] , $auth->user->id , sprintf('%s_%s' , $param['red_packet_id'] , $auth->user->id));
        // 获取红包随机金额
        $money = RedPacketRedis::popByIdentifierAndRedPacketId($auth->identifier , $param['red_packet_id']);
        if ($money === false) {
            // 已经被领取完毕
            return self::error('红包已经被领取完' , 403);
        }
        // 检查当前用户是否领取过该红包
        $red_packet_receive_log = RedPacketReceiveLogModel::findByRedPacketIdAndUserId($param['red_packet_id'] , $auth->user->id);
        if (!empty($red_packet_receive_log)) {
            // 数据库层面限制
            return self::error('你已经领取过该红包' , 403);
        }
        // 保留的小数位数
        $decimal_digit = config('app.decimal_digit');
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
            $time = time();
            $red_packet_expired_duration = config('app.red_packet_expired_duration');
            if (strtotime($red_packet->create_time) + $red_packet_expired_duration < $time) {
                RedPacketModel::updateById($red_packet->id , [
                    'is_expired' => 1
                ]);
                DB::commit();
                return self::error('红包已经过期');
            }
            $group = GroupData::findByIdentifierAndId($auth->identifier , $red_packet->group_id);
            if (empty($group)) {
                return self::error('群不存在' , 404);
            }
            // 检查当前用户是否是群成员
            $member = GroupData::findByIdentifierAndId($auth->identifier , $red_packet->group_id);
            if (empty($member)) {
                return self::error('您并非群成员' , 403);
            }
            // 获取红包金额
            $balance = UserModel::getBalanceByUserIdWithLock($auth->user->id);
            $cur_balance = bcadd($balance , $money);
            $red_packet->received_number++;
            $update_red_packet_data = [
                'is_received'       => intval($red_packet->received_number == $red_packet->number) ,
                'received_number'   => $red_packet->received_number ,
                'received_money'    => bcadd($red_packet->received_money , $money , $decimal_digit)
            ];
            if ($update_red_packet_data['is_received']) {
                // 如果已经过期，更新过期时间
                $update_red_packet_data['received_time'] = date('Y-m-d H:i:s');
            }
            RedPacketModel::updateById($red_packet->id , $update_red_packet_data);
            RedPacketReceiveLogModel::insertGetId([
                'user_id' => $auth->user->id ,
                'identifier' => $auth->identifier ,
                'red_packet_id' => $red_packet->id ,
                'money' => $money ,
            ]);
            FundLogModel::insertGetId([
                'user_id' => $auth->user->id ,
                'identifier' => $auth->identifier ,
                'type' => 'red_packet' ,
                'before' => $balance ,
                'after' => $cur_balance ,
                'money' => bcsub($cur_balance , $balance , $decimal_digit) ,
                'desc' => '领取红包' ,
            ]);
            UserData::updateByIdentifierAndIdAndData($auth->identifier , $auth->user->id , [
                'balance' => $cur_balance
            ]);
            $res = ChatUtil::groupSendForRedPacketStep1($auth , $red_packet->user_id , [
                'user_id' => $auth->user->id ,
                'group_id' => $group->id ,
                'type' => 'notification' ,
                'message' => sprintf('%s 领取了红包' , UserUtil::getNameFromNicknameAndUsername($auth->user->nickname , $auth->user->username)) ,
                'old' => 1 ,
            ]);
            if ($res['code'] != 0) {
                RedPacketRedis::pushByIdentifierAndRedPacketIdAndValAndType($auth->identifier , $param['red_packet_id'] , $money , 'left');
                RedPacketReceivedLogRedis::delByIdentifierAndRedPacketIdAndUserId($auth->identifier , $param['red_packet_id'] , $auth->user->id);
                DB::rollBack();
                return self::error('领取红包失败（发送消息失败：' . $res['data'] . '）');
            }
            DB::commit();
            $res = $res['data'];
            ChatUtil::groupSendForRedPacketStep2($auth , $res['user_ids'] , $res['message']);
            return self::success();
        } catch(Exception $e){
            RedPacketRedis::pushByIdentifierAndRedPacketIdAndValAndType($auth->identifier , $param['red_packet_id'] , $money , 'left');
            RedPacketReceivedLogRedis::delByIdentifierAndRedPacketIdAndUserId($auth->identifier , $param['red_packet_id'] , $auth->user->id);
            var_dump('金额回复 + 数据回复');
            DB::rollBack();
            throw $e;
        }
    }

    // 总计：发出的红包
    public static function redPacketReceivedLog(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'red_packet_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $red_packet = RedPacketModel::findById($param['red_packet_id']);
        if (empty($red_packet)) {
            return self::error('未找到红包信息' , 404);
        }
        $limit = !empty($param['limit']) ? $param['limit'] : config('app.limit');
        $res = RedPacketReceiveLogModel::getByFilterAndLimitIdAndLimit([
            'red_packet_id' => $red_packet->id ,
        ] , (int) $param['limit_id'] , $limit);
        // 手气最佳
        $best_user_id = RedPacketReceiveLogModel::getUserIdForMostMoneyByRedPacketId($red_packet->id);
        foreach ($res as $v)
        {
            $red_packet = RedPacketData::findByIdentifierAndId($v->identifier , $v->red_packet_id);
            UserUtil::handle($red_packet->user , $auth->user->id);
            $v->red_packet = $red_packet;
            $v->best = $v->user_id == $best_user_id ? 1 : 0;
        }
        return self::success($res);
    }


    // 统计信息：收到的红包
    public static function redPacketReceivedInfo(Auth $auth , array $param)
    {
        $param['year'] = empty($param['year']) ? date('Y') : $param['year'];
        $money = RedPacketReceiveLogModel::getMoneyByFilter([
            'user_id' => $auth->user->id ,
            'year' => $param['year']
        ]);
        $money = bcmul($money , 1 , config('app.decimal_digit'));
        $number = RedPacketReceiveLogModel::getNumberByFilter([
            'user_id' => $auth->user->id ,
            'year' => $param['year']
        ]);
        $number_for_best = RedPacketReceiveLogUtil::getNumberForMostMoneyByUserIdAndYear($auth->user->id , $param['year']);
        return self::success([
            'money' => $money ,
            'number' => $number ,
            'number_for_best' => $number_for_best
        ]);
    }

    // 统计信息：发出去的红包
    public static function redPacketSendInfo(Auth $auth , array $param)
    {
        $param['year'] = empty($param['year']) ? date('Y') : $param['year'];
        $money = RedPacketModel::getMoneyByFilter([
            'user_id' => $auth->user->id ,
            'year' => $param['year']
        ]);
        $money = bcmul($money , 1 , config('app.decimal_digit'));
        $number = RedPacketModel::getNumberByFilter([
            'user_id' => $auth->user->id ,
            'year' => $param['year']
        ]);
        return self::success([
            'money' => $money ,
            'number' => $number ,
        ]);
    }

    // 总计：收到的红包记录
    public static function redPacketReceivedLogs(Auth $auth , array $param)
    {
        $param['year'] = empty($param['year']) ? date('Y') : $param['year'];
        $limit = !empty($param['limit']) ? $param['limit'] : config('app.limit');
        $res = RedPacketReceiveLogModel::getByFilterAndLimitIdAndLimit([
            'user_id' => $auth->user->id ,
            'year' => $param['year'] ,
        ] , (int) $param['limit_id'] , $limit);
        foreach ($res as $v)
        {
            $best_user_id = RedPacketReceiveLogModel::getUserIdForMostMoneyByRedPacketId($v->red_packet_id);
            $red_packet = RedPacketData::findByIdentifierAndId($v->identifier , $v->red_packet_id);
            UserUtil::handle($red_packet->user , $auth->user->id);
            $v->red_packet = $red_packet;
            $v->best = $v->user_id == $best_user_id ? 1 : 0;
        }
        return self::success($res);
    }

    // 总计：发出的红包记录
    public static function redPacketSendLogs(Auth $auth , array $param)
    {
        $param['year'] = empty($param['year']) ? date('Y') : $param['year'];
        $limit = !empty($param['limit']) ? $param['limit'] : config('app.limit');
        $res = RedPacketModel::getByFilterAndLimitIdAndLimit([
            'user_id' => $auth->user->id ,
            'year' => $param['year'] ,
        ] , (int) $param['limit_id'] , $limit);
        return self::success($res);
    }
}