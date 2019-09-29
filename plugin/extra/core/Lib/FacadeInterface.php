<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/1
 * Time: 10:43
 */

namespace Core\Lib;

interface FacadeInterface
{
    static function getFacadeAccessor() :string;
}