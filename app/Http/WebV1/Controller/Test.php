<?php


namespace App\Http\WebV1\Controller;


use App\Http\WebV1\Redis\CacheRedis;

class Test extends Common
{
    public function index()
    {
        CacheRedis::value('timer_date' , date('Y-m-d'));
    }
}