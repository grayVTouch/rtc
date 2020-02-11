<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2018/7/21
 * Time: 16:33
 */

namespace extra;

use Exception;

// 字符串长度验证
function check_len($str , $len , $sign = 'eq'){
    $range = ['gt' , 'gte' , 'lt' , 'lte' , 'eq'];
    $sign = in_array($sign , $range) ? $sign : 'eq';
    $str_len = mb_strlen($str);

    switch ($sign)
    {
        case 'gt':
            return $str_len > $len;
        case 'gte':
            return $str_len >= $len;
        case 'lt':
            return $str_len < $len;
        case 'lte':
            return $str_len <= $len;
        case 'eq':
            return $str_len = $len;
        default:
            throw new Exception('不支持的比较符类型');
    }
}

// 检查手机号码
function check_phone($phone , $all = true){
    if (!$all) {
        return (bool) preg_match('/^1\d{10}$/' , $phone);
    }
    return (bool) (preg_match('/^1\d{10}$/' , $phone) || preg_match('/^\d+\-\d+$/' , $phone));
}

// 检查价格
function check_price($price){
    return (bool) preg_match('/^[1-9]?\d*(\.\d{0,2})?$/' , $price);
}

// 检查年份
function check_year($year){
    return check_datetime($year , 'year');
}

function check_month($date)
{
    return check_datetime($date , 'month');
}

function check_date($date)
{
    return check_datetime($date , 'date');
}

function check_hour($date)
{
    return check_datetime($date , 'hour');
}

function check_minute($date)
{
    return check_datetime($date , 'minute');
}

function check_second($date)
{
    return check_datetime($date , 'second');
}

// 检查日期格式
function check_datetime($date , $type = 'all'){
    switch ($type)
    {
        case 'all':
            $reg = '/^\d{4}\-(0[1-9]|1[0-2])\-(0[1-9]|[1-2]\d|3[0-1]) ([01][0-9]|2[0-3])\-[0-5][0-9]\-[0-5][0-9]$/';
            break;
        case 'year':
            $reg = '/^\d{4}$/';
            break;
        case 'month':
            $reg = '/^\d{4}\-(0[1-9]|1[0-2])$/';
            break;
        case 'date':
            $reg = '/^\d{4}\-(0[1-9]|1[0-2])\-(0[1-9]|[1-2]\d|3[0-1])$/';
            break;
        case 'hour':
            $reg = '/^\d{4}\-(0[1-9]|1[0-2])\-(0[1-9]|[1-2]\d|3[0-1]) ([01][0-9]|2[0-3])$/';
            break;
        case 'minute':
            $reg = '/^\d{4}\-(0[1-9]|1[0-2])\-(0[1-9]|[1-2]\d|3[0-1]) ([01][0-9]|2[0-3])\:[0-5][0-9]$/';
            break;
        case 'second':
            $reg = '/^\d{4}\-(0[1-9]|1[0-2])\-(0[1-9]|[1-2]\d|3[0-1]) ([01][0-9]|2[0-3])\:[0-5][0-9]\:[0-5][0-9]$/';
            break;
        default:
            throw new Exception('不支持的 type');
    }
    return (bool) preg_match($reg , $date);
}



// 检查数字
function check_num($num , $len = 0){
    $reg = "/^\d+(\.\d{0,{$len}})?$/";
    return (bool) preg_match($reg , $num);
}

// 检查密码
function check_password($password){
    $reg = "/^.{6,}$/";
    return (bool) preg_match($reg , $password);
}

// 检查电子邮箱
function check_email($mail){
    $reg = "/^\.+@\.+$/";

    return (bool) preg_match($reg , $mail);
}

// 正则验证
function regexp_check(string $reg = '' , string $str = '')
{
    $reg = addslashes($reg);
    $reg = addcslashes($reg , '/[]()-');
    return (bool) preg_match("/{$reg}/" , $str);
}

function check_num_len($num , $len)
{
    return is_numeric($num) && mb_strlen($num) == $len;
}


function is_http($str = '')
{
    $reg = '/^https?:\/\//';
    return (bool) preg_match($reg , $str);
}

function has_cn($str = '')
{
    $reg = '/[\x{4e00}-\x{9fa5}]/u';
    return (bool) preg_match($reg , $str);
}

function has_en($str = '')
{
    $reg = '/[A-z]+/';
    return (bool) preg_match($reg , $str);
}

// 是否全部中文
function all_cn($str = '')
{
    return (bool) preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $str);
}
