<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/10/10
 * Time: 11:07
 */

namespace App\Model;


class ProgramErrorLogModel extends Model
{
    protected $table = 'program_error_log';

    public static function u_insertGetId(string $name , string $detail = '' , string $type = 'common')
    {
        return self::insertGetId([
            'name'      => $name ,
            'detail'    => $detail ,
            'type'      => $type ,
        ]);
    }
}