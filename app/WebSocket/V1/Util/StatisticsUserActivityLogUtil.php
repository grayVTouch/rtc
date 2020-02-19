<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2020/2/19
 * Time: 11:23
 */

namespace App\WebSocket\V1\Util;


use App\WebSocket\V1\Model\StatisticsUserActivityLogModel;

class StatisticsUserActivityLogUtil extends Util
{
    public static function createOrUpdateCountByIdentifierAndDateAndData(string $identifier , string $date , array $data)
    {
        $res = StatisticsUserActivityLogModel::findByIdentifierAndDate($identifier , $date);
        $field = ['user_count' , 'client_count'];
        if (empty($res)) {
            $update = [
                'identifier' => $identifier ,
                'date' => $date
            ];
            foreach ($data as $k => $v)
            {
                if (!in_array($k , $field)) {
                    return self::error('不支持的字段，当前受支持的字段有：' . implode(',' , $field));
                }
                if (!in_array($v , $mode)) {
                    return self::error('不支持的模式，当前受支持的模式有：' . implode(',' , $mode));
                }
                // ['login_count' => 'add' , 'logout_count' => 'sub']
                // 仅有 add-新增 | sub-减少
                $update[$k] = $v == 'inc' ? 1 : 0;
            }
            StatisticsUserActivityLogModel::insertGetId($update);
            return self::success();
        }
        $update = [];
        foreach ($data as $k => $v)
        {
            if (!in_array($k , $field)) {
                return self::error('不支持的字段，当前受支持的字段有：' . implode(',' , $field));
            }
            if (!in_array($v , $mode)) {
                return self::error('不支持的模式，当前受支持的模式有：' . implode(',' , $mode));
            }
            // ['login_count' => 'add' , 'logout_count' => 'sub']
            // 仅有 add-新增 | sub-减少
            $update[$k] = max(0 , $v == 'inc' ? $res->$k++ : $res->$k--);
        }
        StatisticsUserActivityLogModel::updateById($res->id , $update);
        return self::success();
    }
}