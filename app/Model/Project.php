<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/10
 * Time: 16:29
 */

namespace App\Model;


class Project extends Model
{
    protected $table = 'project';
    public $timestamps = false;

    public static function findByIdentifier(string $identifier = '')
    {
        $res = self::where('identifier' , $identifier)
            ->first();
        self::single($res);
        return $res;
    }

    public static function u_insertGetId(string $name , string $identifier)
    {
        return self::insertGetId([
            'name' => $name ,
            'identifier' => $identifier
        ]);
    }
}