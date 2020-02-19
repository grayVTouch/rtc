<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2020/2/12
 * Time: 12:06
 */

namespace App\WebSocket\V1\Model;


use function core\convert_obj;
use Illuminate\Support\Facades\DB;

class RedPacketModel extends Model
{
    protected $table = 'red_packet';

    public static function findByIdWithLock(int $id)
    {
        $res = self::lockForUpdate()
            ->find($id);
        if (empty($res)) {
            return ;
        }
        self::single($res);
        return $res;
    }

    public static function getByFilterAndLimitIdAndLimit(array $filter = [] , int $limit_id = 0 , int $limit = 20)
    {
        $filter['user_id'] = $filter['user_id'] ?? '';
        $filter['year'] = $filter['year'] ?? '';
        $where = [];
        if ($filter['user_id'] != '') {
            $where[] = ['user_id' , '=' , $filter['user_id']];
        }
        if ($limit_id) {
            $where[] = ['id' , '<' , $limit_id];
        }
        $model = self::where($where);
        if ($filter['year'] != '') {
            $model->whereRaw(DB::raw('date_format(create_time , "%Y") = "' . $filter['year'] . '"'));
        }
        $res = $model->limit($limit)
            ->get();
        $res = convert_obj($res);
        foreach ($res  as $v)
        {
            self::single($v);
        }
        return $res;
    }

    public static function getMoneyByFilter(array $filter = [])
    {
        $filter['user_id'] = $filter['user_id'] ?? '';
        $filter['year'] = $filter['year'] ?? '';
        $where = [];
        if ($filter['user_id'] != '') {
            $where[] = ['user_id' , '=' , $filter['user_id']];
        }
        $model = self::where($where);
        if ($filter['year'] != '') {
            $model->whereRaw(DB::raw('date_format(create_time , "%Y") = "' . $filter['year'] . '"'));
        }
        return $model->sum('received_money');
    }

    public static function getNumberByFilter(array $filter = [])
    {
        $filter['user_id'] = $filter['user_id'] ?? '';
        $filter['year'] = $filter['year'] ?? '';
        $where = [];
        if ($filter['user_id'] != '') {
            $where[] = ['user_id' , '=' , $filter['user_id']];
        }
        $model = self::where($where);
        if ($filter['year'] != '') {
            $model->whereRaw(DB::raw('date_format(create_time , "%Y") = "' . $filter['year'] . '"'));
        }
        return $model->count();
    }

    public static function getByUserId(int $user_id)
    {
        $res = self::where('user_id' , $user_id)
            ->get();
        foreach ($res  as $v)
        {
            self::single($v);
        }
        return $res;
    }

    public static function delByUserId(int $user_id)
    {
        return self::where('user_id' , $user_id)
            ->delete();
    }

    // 获取未过期的红包
    public static function notExpiredRedPacket()
    {
        $res = self::where('is_expired' , 0)->get();
        foreach ($res  as $v)
        {
            self::single($v);
        }
        return $res;
    }

    // 获取过期未退款的红包
    public static function expiredAndReceivedAndNotRefundRedPacket()
    {
        $res = self::where([
            ['is_expired' , '=' , 1] ,
            ['is_received' , '=' , 0] ,
            ['is_refund' , '=' , 0] ,
        ])->get();
        foreach ($res  as $v)
        {
            self::single($v);
        }
        return $res;
    }
}