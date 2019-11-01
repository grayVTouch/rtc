<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/12
 * Time: 22:36
 */


$group = [
    'a' ,
    'b' ,
    'c'
];

foreach ($group as &$v)
{
    $v = '|---' . $v;
}

foreach ($group as $v)
{
    var_dump($v);
}