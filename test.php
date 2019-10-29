<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/12
 * Time: 22:36
 */


$str = '你好吗nihao很好，非常耗；你好nihao你好吗';

var_dump('源字符串：' . $str);
$reg = '/([\x{4e00}-\x{9fa5}]+)/u';
preg_match_all($reg , $str , $matches);


print_r($matches);
array_shift($matches);
//print_r($matches);

$matches = $matches[0];

foreach ($matches as &$v)
{
    $v = "[翻译后的字符串：$v]";
}

$count = 0;
$replace = preg_replace_callback($reg , function($v) use($matches , &$count){
    return $matches[$count++];
} , $str);

var_dump($replace);