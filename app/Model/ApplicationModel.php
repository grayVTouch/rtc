<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/11
 * Time: 16:00
 */

namespace App\Model;


use function core\convert_obj;
use Exception;

class ApplicationModel extends Model
{
    protected $table = 'application';

    public static function list(array $filter = [] , array $order = [] , int $limit = 20)
    {
        $filter['id'] = $filter['id'] ?? '';
        $order['field'] = $order['field'] ?? 'id';
        $order['value'] = $order['field'] ?? 'desc';
        $where = [];
        if ($filter['id'] != '') {
            $where[] = ['id' , '=' , $filter['id']];
        }
        $res = self::with(['user'])
            ->where($where)
            ->orderBy($order['field'] , $order['value'])
            ->paginate($limit);
        $res = convert_obj($res);
        foreach ($res as $v)
        {
            self::single($v);
            UserModel::single($v->user);
        }
        return $res;
    }

    /**
     * @param int $user_id
     * @param int $is_read
     * @return int
     * @throws Exception
     */
    public static function countByUserIdAndIsRead(int $user_id , int $is_read)
    {
        $range = [0,1];
        if (!in_array($is_read , $range)) {
            throw new Exception('不支持的类型，当前受支持的类型有：' . implode(',' , $range));
        }
        if ($is_read) {
            // 申请状态：approve-同意；refuse-拒绝；wait-等待处理',
            return (int) (self::where('user_id' , $user_id)
                ->whereIn('status' , ['approve' , 'refuse'])
                ->count());
        }
        return (int) (self::where('user_id' , $user_id)
            ->whereIn('status' , ['wait'])
            ->count());
    }
}