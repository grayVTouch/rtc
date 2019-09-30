<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/30
 * Time: 14:05
 */

namespace App\Model;


class BlacklistModel extends Model
{
    protected $table = 'blacklist';

    public static function u_insertGetId(int $user_id , int $block_user_id)
    {
        return self::insertGetId([
            'user_id'       => $user_id ,
            'block_user_id' => $block_user_id
        ]);
    }
}