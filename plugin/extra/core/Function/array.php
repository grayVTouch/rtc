<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2018/1/23
 * Time: 11:34
 */

namespace core;

// 提取数组指定长度的单元
// 适用于 一维数组
function chunk(array $data = [] , $size = 10){
    $res = [];
    $i   = 0;

    for ($i = 0; $i < count($data); ++$i)
    {
        if ($i < $size) {
            $res[] = $data[$i];
        } else {
            break;
        }
    }



    return $res;
}

// StdClass 转换成数组
function obj_to_array($obj){
    return json_decode(json_encode($obj) , true);
}

// 获取给定数组中给定键名对应单元
function array_unit(array $arr = [] , array $keys = [])
{
    $res = [];
    foreach ($keys as $v)
    {
        if (!isset($arr[$v])) {
            continue ;
        }
        $res[$v] = $arr[$v];
    }
    return $res;
}

// 检查某个数组是否包含重复的值（仅适用于一维数组）
function has_repeat_in_array(array $arr)
{
    $res = [];
    foreach ($arr as $v)
    {
        if (isset($res[$v])) {
            $res[$v]++;
        } else {
            $res[$v] = 1;
        }
        if ($res[$v] > 1) {
            return true;
        }
    }
    return false;
}