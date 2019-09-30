<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/29
 * Time: 14:04
 */

namespace App\Util;


use function core\obj_to_array;

class PageUtil
{
    // 默认分页数量
    private static $limit = 20;

    // 默认页数
    private static $page = 1;

    // 默认总记录数
    private static $total = 0;

    // 默认：最小页数
    private static $minPage = 1;

    // 处理
    public static function deal(int $total = 0 , $page = 1 , $limit = 20): array
    {
        $total  = empty($total) ? self::$total : $total;
        $limit  = empty($limit) ? self::$limit : $limit;
        $page   = empty($page) ? self::$page : $page;
        $min_page = self::$minPage;
        $max_page = ceil($total / $limit);
        $offset = ($page - 1) * $limit;
        return [
            'offset' => $offset ,
            'limit' => $limit ,
            'min_page' => $min_page ,
            'max_page' => $max_page ,
            'total' => $total ,
        ];
    }

    // 数据包装
    public static function data($origin , $merge): array
    {
        $origin = obj_to_array($origin);
        $merge = obj_to_array($merge);
        return array_merge($origin , [
            'data' => $merge
        ]);
    }
}