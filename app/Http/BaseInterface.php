<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/11
 * Time: 17:19
 */

namespace App\Http;


interface BaseInterface
{
    function before() :bool;
    function after() :void;
}