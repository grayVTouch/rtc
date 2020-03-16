<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/10
 * Time: 15:15
 */

namespace Engine\Facade;

use Core\Lib\Facade;

/**
 * @see \Engine\WebSocket
 *
 * @method static push(int $fd , string $data = '')
 * @method static exist(int $fd)
 * @method static clearRedisV0(int $user_id , int $fd = null)
 * @method static clearRedisV1(int $user_id , int $fd = null)
 * @method static deliveryTask(string $data = null)
 *
 */

class WebSocket extends Facade
{
    public static function getFacadeAccessor() :string
    {
        return 'websocket';
    }
}