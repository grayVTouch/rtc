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

class RedPacketReceiveLogModel extends Model
{
    protected $table = 'red_packet_receive_log';

    // 查询红包
    public static function findByRedPacketIdAndUserId(int $red_packet_id , int $user_id)
    {
        $res = self::where([
                ['red_packet_id' , '=' , $red_packet_id] ,
                ['user_id' , '=' , $user_id] ,
            ])
            ->first();
        self::single($res);
        return $res;
    }

    public static function isReceivedByUserIdAndRedPacketId(int $red_packet_id , int $user_id)
    {
        return !empty(self::findByRedPacketIdAndUserId($red_packet_id , $user_id));
    }

    public static function getByFilterAndLimitIdAndLimit(array $filter = [] , int $limit_id = 0 , int $limit = 20)
    {
        $filter['user_id'] = $filter['user_id'] ?? '';
        $filter['red_packet_id'] = $filter['red_packet_id'] ?? '';
        $filter['year'] = $filter['year'] ?? '';
        $where = [];
        if ($filter['user_id'] != '') {
            $where[] = ['user_id' , '=' , $filter['user_id']];
        }
        if ($filter['red_packet_id'] != '') {
            $where[] = ['red_packet_id' , '=' , $filter['red_packet_id']];
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

    public static function getUserIdForMostMoneyByRedPacketId(int $red_packet_id)
    {
        return self::where('red_packet_id' , $red_packet_id)
            ->orderBy('money' , 'desc')
            ->limit(1)
            ->value('user_id');
    }

    public static function getMoneyByFilter(array $filter = [])
    {
        $filter['user_id'] = $filter['user_id'] ?? '';
        $filter['red_packet_id'] = $filter['red_packet_id'] ?? '';
        $filter['year'] = $filter['year'] ?? '';
        $where = [];
        if ($filter['user_id'] != '') {
            $where[] = ['user_id' , '=' , $filter['user_id']];
        }
        if ($filter['red_packet_id'] != '') {
            $where[] = ['red_packet_id' , '=' , $filter['red_packet_id']];
        }
        $model = self::where($where);
        if ($filter['year'] != '') {
            $model->whereRaw(DB::raw('date_format(create_time , "%Y") = "' . $filter['year'] . '"'));
        }
        return $model->sum('money');
    }

    public static function getNumberByFilter(array $filter = [])
    {
        $filter['user_id'] = $filter['user_id'] ?? '';
        $filter['red_packet_id'] = $filter['red_packet_id'] ?? '';
        $where = [];
        if ($filter['user_id'] != '') {
            $where[] = ['user_id' , '=' , $filter['user_id']];
        }
        if ($filter['red_packet_id'] != '') {
            $where[] = ['red_packet_id' , '=' , $filter['red_packet_id']];
        }
        $model = self::where($where);
        if ($filter['year'] != '') {
            $model->whereRaw(DB::raw('date_format(create_time , "%Y") = "' . $filter['year'] . '"'));
        }
        return $model->count();
    }

    public static function getByUserId(int $user_id)
    {
        $res = self::where('user_id', $user_id)
            ->get();
        $res = convert_obj($res);
        foreach ($res as $v)
        {
            self::single($v);
        }
        return $res;
    }

    public static function getByUserIdAndTypeAndYear(int $user_id , string $type , string $year)
    {
        $res = self::from('red_packet_receive_log as rprl')
            ->leftJoin('red_packet as rp' , 'rprl.red_packet_id' , '=' , 'rp.id')
            ->where([
                ['rprl.user_id' , '=' , $user_id] ,
                ['rp.type' , '=' , $type] ,
            ])
            ->whereRaw(DB::raw("date_format(rtc_rprl.create_time , '%Y') = '{$year}'"))
            ->select('rprl.*')
            ->get();
        $res = convert_obj($res);
        foreach ($res as $v)
        {
            self::single($v);
        }
        return $res;
    }

    public static function delByRedPacketIds(array $red_packet_ids)
    {
        return self::whereIn('red_packet_id' , $red_packet_ids)
            ->delete();
    }
}