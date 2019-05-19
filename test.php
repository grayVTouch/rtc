<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/18
 * Time: 22:55
 */


use Swoole\Timer;
use Swoole\WebSocket\Server as WebSocket;

$ws = new WebSocket('0.0.0.0' , 9300);

$ws->on('WorkerStart' , function($server , $worker_id){
//    var_dump($worker_id);
    if ($worker_id == 0) {
        Timer::tick(2 * 1000 , function(){
            var_dump('hello');
        });


        Timer::tick(3 * 1000 , function (){
            var_dump('nihao');
        });
    }
});

$ws->on('message' , function(){

});

$ws->on('open' , function(){

});


$ws->on('close' , function(){

});



$ws->start();

exit(0);